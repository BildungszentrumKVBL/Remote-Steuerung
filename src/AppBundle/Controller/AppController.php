<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AbstractCommand;
use AppBundle\Entity\Building;
use AppBundle\Entity\Device;
use AppBundle\Entity\EventGhostCommand;
use AppBundle\Entity\Log;
use AppBundle\Entity\Room;
use AppBundle\Entity\User;
use AppBundle\Entity\View;
use AppBundle\Entity\Zulu;
use AppBundle\Entity\ZuluCommand;
use AppBundle\EventDispatcher\Event\CommandEvent;
use Detection\MobileDetect;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Gos\Component\WebSocketClient\Exception\BadResponseException;

/**
 * Class AppController.
 *
 * This controller is the main controller of this application. It serves general sites, like the typical `index.html`
 * and application-relevant sites.
 */
class AppController extends Controller
{
    /**
     * This route serves the index page.
     *
     * @Route("/", name="homepage")
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        return $this->render('AppBundle:app:index.html.twig');
    }

    /**
     * This route handles the reservation of the rooms.
     * It automatically unlocks the room the user already reserved, after he chose a new room.
     * Rooms that are already in use will not be displayed as an option to reserve, except the user that is requesting
     * this route is going to use this room according to WebUntis.
     * When the user will be giving a lecture according to WebUntis, the room is pre-selected for him.
     *
     * @Route("/chooseRoom", name="choose_room_route")
     * @Security("has_role('ROLE_TEACHER')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function chooseRoomAction(Request $request): Response
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $roomname = $this->get('app.webuntis.handler')->login()->getRoomForTeacher($this->getUser()->getUsername());
        $building = null;
        if ($roomname) {
            /* @var Zulu $zulu */
            $zulu = $em->getRepository('AppBundle:Zulu')->findOneBy(['room' => $roomname]);
            if ($zulu) {
                $building = $zulu->getRoom()->getBuilding();
            }
        }

        $repo = $em->getRepository('AppBundle:Zulu');

        $values = $request->request;
        if ($values->get('senden')) {

            // Unlocks Zulu that he locked
            $lockedZulu = $repo->findOneBy(['lockedBy' => $this->getUser()->getUsername()]);
            if ($lockedZulu) {
                $lockedZulu->unlock();
                $em->persist($lockedZulu);
                $em->flush();
            }

            $room = $this->get('doctrine.orm.entity_manager')->getRepository('AppBundle:Room')->findOneBy(['name' => $values->get('room')]);
            // Locks new Zulu
            /* @var Zulu $zulu */
            $zulu = $repo->findOneBy(['room' => $room]);
            if ($zulu) {
                $zulu->lock($this->getUser());
                $em->persist($zulu);
                $em->flush();

                return $this->redirectToRoute('controller_route');
            }
        }

        $zulus     = $repo->findAll();
        $buildings = [];
        $rooms     = [];
        foreach ($zulus as $zulu) {
            /* @var Zulu $zulu */
            $room = $zulu->getRoom();

            $buildings[] = $room->getBuilding(); // If one building is completely reserved, it will not display.
            $rooms[]     = $room;
        }
        if ($roomname) { // Add room requested in WebUntis
            $rooms[] = $roomname;
        }
        $buildings = array_unique($buildings);

        return $this->render(
            'AppBundle:app:chooseRoom.html.twig', [
                'buildings' => $buildings,
                'rooms'     => $rooms,
                'roomname'  => $roomname,
                'building'  => $building,
            ]
        );
    }

    /**
     * This room gets the zulus, after the building has been selected.
     *
     * @Route("/chooseRoom/{building}", name="get_zulus_route", options={"expose"=true})
     * @ParamConverter("building", class="AppBundle:Building", options={"mapping": {"building" = "name"}})
     * @Security("has_role('ROLE_TEACHER')")
     * @Method(methods={"GET"})
     *
     * @param Building $building
     *
     * @return Response
     */
    public function getZulusAction(Building $building): Response
    {
        // Get all rooms that are free and the room booked by WebUntis.
        $em       = $this->get('doctrine.orm.entity_manager');
        $roomname = $this->get('app.webuntis.handler')->login()->getRoomForTeacher($this->getUser()->getUsername());
        /* @var Room $room */
        $room  = $em->getRepository('AppBundle:Room')->findOneBy(['name' => $roomname]);
        $zulus = $em->createQueryBuilder()->select('z')->from('AppBundle:Zulu', 'z')->join('z.room', 'r')->where(
            'r.building = :building'
        )->andWhere('z.locked = false')->orWhere('r = :room')->setParameters(
            [
                'building' => $building,
                'room'     => $room,
            ]
        )->getQuery()->getResult();

        return new JsonResponse($zulus, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * This controller serves the controller page for the user that wants to control the room he reserved.
     * When the user did not choose the room, he will be redirected to the `AppController::chooseRoomAction`.
     *
     * @Route("/controller/{view}", name="controller_route", defaults={"view": null}, requirements={"view": "\d+"},
     *                              options={"expose": true})
     * @ParamConverter("view", class="AppBundle:View")
     * @Security("has_role('ROLE_TEACHER')")
     *
     * @param Request $request
     * @param View    $view
     *
     * @return Response
     */
    public function controllerAction(Request $request, $view): Response
    {
        $em   = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();
        $zulu = $em->getRepository('AppBundle:Zulu')->findOneBy(['lockedBy' => $user->getUsername()]);
        /* @var User $user */
        /* @var Zulu $zulu */

        if (!$zulu) {
            return $this->redirectToRoute('choose_room_route');
        }

        $status = $this->get('command_handler')->getStatusOfZulu();

        if ($view) {
            /** @var View $view */
            $user->getSettings()->setView($view);
        }

        try {
            $this->get('gos_web_socket.wamp.pusher')->push(
                [
                    'action' => 'updateView',
                    'data'   => [
                        'id'   => $user->getId(),
                        'view' => $user->getSettings()->getView()->jsonSerialize(),
                    ],
                ], 'command_topic'
            );
        } catch (BadResponseException $e) {
            $log = new Log(sprintf('Exception was caught: %s', $e->getMessage()), Log::LEVEL_ERROR, $user);
            $em->persist($log);
            $em->flush();
        }


        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle:app/content:controller.html.twig', ['status' => $status]);
        }

        return $this->render(
            'AppBundle:app:controller.html.twig', [
                'status' => $status,
                'room'   => $zulu->getRoom(),
            ]
        );
    }

    /**
     * This route will serve the settings page for the individual user and handles the requested changes.
     *
     * @Route("/settings", name="user_settings_route")
     * @Security("has_role('ROLE_TEACHER')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function settingsAction(Request $request): Response
    {
        /* @var User $user */
        $em       = $this->get('doctrine.orm.entity_manager');
        $user     = $this->getUser();
        $settings = $user->getSettings();
        $values   = $request->request;

        if ($values->get('senden')) {
            $settings->setUsePush((bool) $values->get('usePush'));
            $settings->setTheme($values->get('design'));

            /** @var View $view */
            $view = $em->getRepository('AppBundle:View')->findOneBy(['name' => $values->get('view')]);
            $settings->setView($view);

            $em->persist($settings);
            $em->flush();
        }
        $views = $em->getRepository('AppBundle:View')->findAll();

        return $this->render(
            'AppBundle:app:settings.html.twig', [
                'views'      => $views,
                'activeView' => $settings->getView(),
            ]
        );
    }

    /**
     * @Route("/settings/notification/{state}/{token}", name="notification_settings_route", options={"expose": true},
     *                                                  requirements={"state": "[0-1]{1}"})
     * @Security("has_role('ROLE_TEACHER')")
     * @Method(methods={"post"})
     * TODO: Add ParamConverter.
     *
     * @param Request $request
     * @param bool    $state
     * @param string  $token
     *
     * @return Response
     */
    public function notificationSettingsAction(Request $request, bool $state, string $token): Response
    {
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user   = $this->getUser();
        $device = $em->getRepository('AppBundle:Device')->findOneBy(['messagingId' => $token]);
        $user->getSettings()->setUsePush($state);
        if (!$device) {
            $detector = new MobileDetect();
            $type     = Device::TYPE_WEB;
            if ($detector->isMobile()) {
                if ($detector->is('iOS')) {
                    $type = Device::TYPE_IOS;
                } elseif ($detector->is('Android')) {
                    $type = Device::TYPE_ANDROID;
                }
            }
            $device = new Device($request->headers->get('User-Agent'), $token, $type);
            $user->addDevice($device);
        }
        $em->persist($user);
        $em->flush();

        $text = $state ? 'Deaktivieren' : 'Aktivieren';

        return new JsonResponse(['state' => $state, 'text' => $text]);
    }

    /**
     * This route is the core of the command-functionality.
     *
     * It get the requested command from the user, handles it and informs the it via the `command_topic`-channel on the
     * websocket-connection.
     *
     * After the command has been executed, it sends a json response to the front-end, which will contain information
     * such as:
     *  - `type`: If it was successful or not.
     *  - `action`: The action that the front end has to make. e.g. Update the status of the zulu buttons.
     *  - `data`: Additional data that the `action` needs in order to work.
     *
     * @Route("/command/{command}", name="send_commands_route", options={"expose"=true})
     * @ParamConverter("command", class="AppBundle:AbstractCommand", options={"mapping":{"command": "name"}})
     * @Security("has_role('ROLE_TEACHER')")
     * @Method(methods={"post"})
     *
     *
     * @param Request         $request
     * @param AbstractCommand $command
     *
     * @return Response
     */
    public function commandsAction(Request $request, AbstractCommand $command): Response
    {
        $commandHandler = $this->get('command_handler');
        if ($command instanceof EventGhostCommand) {
            $additionalData = $request->request->all();
            $command->setAdditionalData($additionalData);
        }
        $dispatcher = $this->get('event_dispatcher');
        $status     = $commandHandler->getStatusOfZulu();
        $event      = new CommandEvent();
        $event->setStatus($status);
        /** @var User $user */
        $user = $this->getUser();
        $event->setUser($user);
        $event->setCommand($command);
        $dispatcher->dispatch('app.command_event', $event);

        try {
            $commandHandler->runCommand($command);
        } catch (\Exception $e) {

            return new JsonResponse(['type' => 'error'], Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }
        $response = new JsonResponse(['type' => 'success'], Response::HTTP_OK, ['Content-Type' => 'application/json']);
        if ($command instanceof ZuluCommand) {
            $response->setData(
                [
                    'type'   => 'success',
                    'action' => 'UpdateController',
                    'data'   => $status,
                ]
            );
        }

        return $response;
    }

    /**
     * @Route("/favicon.ico", name="favicon_route")
     *
     * @return Response
     */
    public function faviconAction(): Response
    {
        $icon = $this->get('kernel')->locateResource('@AppBundle/Resources/public/assets/favicon.ico');

        return new Response(file_get_contents($icon), Response::HTTP_OK, ['content-type' => 'image/x-icon']);
    }

    /**
     * This route serves the `manifest.json`-file which is needed in order to make an offline website.
     *
     * @Route("/assets/manifest.json", name="app_manifest_route")
     *
     * @return Response
     */
    public function jsonManifestAction(): Response
    {
        return $this->render('@App/app/manifest.json.twig');
    }

    /**
     * @Route("/firebase-messaging-sw.js", name="service_worker_route", options={"expose": true})
     *
     * @return Response
     */
    public function serviceWorkerAction(): Response
    {
        $asseticManager = $this->get('assetic.asset_manager');
        $names          = $asseticManager->getNames();
        $assets         = [];

        foreach ($names as $name) {
            $output = $asseticManager->getFormula($name)[2]['output'];

            if (substr($output, -3) === '.js' || substr($output, -4) === '.css') {
                $assets[] = str_replace('_controller', '', $output);
            }
        }

        return new Response(
            $this->renderView(
                '@App/app/service-worker.js.twig',
                ['assets' => $assets]
            ), Response::HTTP_OK, ['content-type' => 'application/javascript']
        );
    }
}
