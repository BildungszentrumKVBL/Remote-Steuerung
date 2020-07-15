<?php

namespace App\Controller;

use App\Entity\AbstractCommand;
use App\Entity\Building;
use App\Entity\Device;
use App\Entity\EventGhostCommand;
use App\Entity\Log;
use App\Entity\Room;
use App\Entity\User;
use App\Entity\View;
use App\Entity\Zulu;
use App\Entity\ZuluCommand;
use App\EventDispatcher\Event\CommandEvent;
use App\Service\CommandsHandler;
use App\Service\WebUntisHandler;
use Detection\MobileDetect;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;
use Gos\Component\WebSocketClient\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AppController.
 *
 * This controller is the main controller of this application. It serves general sites, like the typical `index.html`
 * and application-relevant sites.
 */
class AppController extends AbstractController
{
    /**
     * This route serves the index page.
     *
     * @Route("/", name="homepage")
     */
    public function indexAction(): Response
    {
        return $this->render('app/index.html.twig');
    }

    /**
     * This route handles the reservation of the rooms.
     * It automatically unlocks the room the user already reserved, after he chose a new room.
     * Rooms that are already in use will not be displayed as an option to reserve, except the user that is requesting
     * this route is going to use this room according to WebUntis.
     * When the user will be giving a lecture according to WebUntis, the room is pre-selected for him.
     *
     * @Route("/chooseRoom", name="choose_room_route")
     * @Security("is_granted('ROLE_TEACHER')")
     */
    public function chooseRoomAction(Request $request, WebUntisHandler $webUntisHandler, EntityManagerInterface $em): Response
    {
        $roomname = $webUntisHandler->login()->getRoomForTeacher($this->getUser()->getUsername());
        $building = null;
        if ($roomname) {
            /* @var Zulu $zulu */
            $zulu = $em->getRepository(Zulu::class)->findOneBy(['room' => $roomname]);
            if (null !== $zulu) {
                $building = $zulu->getRoom()->getBuilding();
            }
        }

        $repo = $em->getRepository(Zulu::class);

        $values = $request->request;
        if ($values->get('senden')) {
            // Unlocks Zulu that he locked
            $lockedZulu = $repo->findOneBy(['lockedBy' => $this->getUser()->getUsername()]);
            if ($lockedZulu) {
                $lockedZulu->unlock();
                $em->persist($lockedZulu);
                $em->flush();
            }

            $room = $em->getRepository(Room::class)->findOneBy(['name' => $values->get('room')]);
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
            'app/chooseRoom.html.twig', [
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
     * @ParamConverter("building", class="App\Entity\Building", options={"mapping": {"building" = "name"}})
     * @Security("has_role('ROLE_TEACHER')")
     * @Method(methods={"GET"})
     */
    public function getZulusAction(Building $building, EntityManagerInterface $em, WebUntisHandler $webUntisHandler): Response
    {
        // Get all rooms that are free and the room booked by WebUntis.
        $roomname = $webUntisHandler->login()->getRoomForTeacher($this->getUser()->getUsername());
        /* @var Room $room */
        $room  = $em->getRepository(Room::class)->findOneBy(['name' => $roomname]);
        $zulus = $em->createQueryBuilder()->select('z')->from(Zulu::class, 'z')->join('z.room', 'r')->where(
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
     * @Route("/controller/{?view}", name="controller_route", requirements={"view": "\d+"}, options={"expose": true})
     *
     * @Security("is_granted('ROLE_TEACHER')")
     */
    public function controllerAction(?View $view, PusherInterface $pusher, CommandsHandler $commandHandler, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $zulu = $em->getRepository(Zulu::class)->findOneBy(['lockedBy' => $user->getUsername()]);
        /* @var User $user */
        /* @var Zulu $zulu */

        if (!$zulu) {
            return $this->redirectToRoute('choose_room_route');
        }

        $status = $commandHandler->getStatusOfZulu();

        if ($view) {
            $user->getSettings()->setView($view);
        }

        try {
            $pusher->push(
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

        return $this->render(
            'app/controller.html.twig', [
                'status' => $status,
            ]
        );
    }

    /**
     * This route will serve the settings page for the individual user and handles the requested changes.
     *
     * @Route("/settings", name="user_settings_route")
     *
     * @Security("is_granted('ROLE_TEACHER')")
     */
    public function settingsAction(Request $request, EntityManagerInterface $em): Response
    {
        /* @var User $user */
        $user     = $this->getUser();
        $settings = $user->getSettings();
        $values   = $request->request;

        if ($values->get('senden')) {
            $settings->setUsePush((bool) $values->get('usePush'));
            $settings->setTheme($values->get('design'));

            /** @var View $view */
            $view = $em->getRepository(View::class)->findOneBy(['name' => $values->get('view')]);
            $settings->setView($view);

            $em->persist($settings);
            $em->flush();
        }
        $views = $em->getRepository(View::class)->findAll();

        return $this->render(
            'app/settings.html.twig', [
                'views'      => $views,
                'activeView' => $settings->getView(),
            ]
        );
    }

    /**
     * @Route("/settings/notification/{state}/{token}", name="notification_settings_route", options={"expose": true},
     *                                                  requirements={"state": "[0-1]{1}"})
     * @Security("has_role('ROLE_TEACHER')")
     */
    public function notificationSettingsAction(Request $request, EntityManagerInterface $em, TranslatorInterface $translator, bool $state, string $token): Response
    {
        /** @var User $user */
        $user   = $this->getUser();
        $device = $em->getRepository(Device::class)->findOneBy(['messagingId' => $token]);
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

        $text = $translator->trans($state ? 'deactivate' : 'activate');

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
     * @ParamConverter("command", class="App\Entity\AbstractCommand", options={"mapping":{"command": "name"}})
     * @Security("has_role('ROLE_TEACHER')")
     * @Method(methods={"post"})
     */
    public function commandsAction(Request $request, AbstractCommand $command, CommandsHandler $commandHandler, EventDispatcherInterface $dispatcher, LoggerInterface $logger): Response
    {
        if ($command instanceof EventGhostCommand) {
            $additionalData = $request->request->all();
            $command->setAdditionalData($additionalData);
        }
        $status     = $commandHandler->getStatusOfZulu();
        $event      = new CommandEvent();
        $event->setStatus($status);
        /** @var User $user */
        $user = $this->getUser();
        $event->setUser($user);
        $event->setCommand($command);
        $dispatcher->dispatch($event);

        try {
            $commandHandler->runCommand($command);
        } catch (Exception $e) {
            $logger->error($e->getMessage());

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
     * This route serves the `manifest.json`-file which is needed in order to make an offline website.
     *
     * @Route("/assets/manifest.json", name="app_manifest_route")
     */
    public function jsonManifestAction(): Response
    {
        return $this->render('app/manifest.json.twig');
    }
}
