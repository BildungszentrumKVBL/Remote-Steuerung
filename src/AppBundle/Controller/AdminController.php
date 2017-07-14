<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Building;
use AppBundle\Entity\User;
use AppBundle\Entity\View;
use AppBundle\Entity\Zulu;
use AppBundle\Service\SettingsHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController.
 *
 * This controller serves all pages that are accessible for the it and hw. For more details see the firewall section in
 * the`./app/config/security.yml`.
 *
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * Serves the observation page for the administrators.
     * It gathers all zulus that are locked and passes them with their status to the `observe.html.twig`.
     *
     * @Route("/observe", name="admin_observe_route", options={"expose": true})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function observeAction(Request $request): Response
    {
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var Zulu[] $lockedZulus */
        $lockedZulus = $em->getRepository('AppBundle:Zulu')->findBy(['locked' => true], ['room' => 'ASC']);
        $handler     = $this->get('command_handler');
        $activeUsers = [];
        foreach ($lockedZulus as $lockedZulu) {
            $user          = $em->getRepository('AppBundle:User')->findOneBy(['username' => $lockedZulu->getLockedBy()]);
            $activeUsers[] = [
                'user'   => $user,
                'zulu'   => $lockedZulu,
                'status' => $handler->setZulu($lockedZulu)->getStatusOfZulu(),
            ];
        }
        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle:admin/content:observe.html.twig', ['activeUsers' => $activeUsers]);
        }

        return $this->render('AppBundle:admin:observe.html.twig', ['activeUsers' => $activeUsers]);
    }

    /**
     * When a administrator's client is being informed about a new reservation of a zulu, it will request the zulu in
     * this route. It sends the `id` of the zulu that will join the observation-interface.
     *
     * @Route(
     *     "/observe/{zuluId}",
     *     name="admin_observe_new_route",
     *     options={"expose": true}
     * )
     * @ParamConverter("zulu", class="AppBundle:Zulu", options={"id" = "zuluId"})
     *
     * @param Zulu $zulu
     *
     * @return Response
     */
    public function observeNewAction(Zulu $zulu): Response
    {
        $em   = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('AppBundle:User')->findOneBy(['username' => $zulu->getLockedBy()]);

        return $this->render('@App/admin/snippets/observation.html.twig', ['user' => $user, 'zulu' => $zulu]);
    }

    /**
     * @Route(
     *     "/observer/update/{userId}/{viewId}",
     *     name="admin_observe_update_route",
     *     options={"expose": true},
     *     requirements={"userId": "\d+", "viewId": "\d+"}
     * )
     * @ParamConverter("user", class="AppBundle:User", options={"id" = "userId"})
     * @ParamConverter("view", class="AppBundle:View", options={"id" = "viewId"})
     *
     *
     * @param User $user
     * @param View $view
     *
     * @return Response
     */
    public function updateObservedAction(User $user, View $view): Response
    {
        $observing = true;
        $em        = $this->get('doctrine.orm.entity_manager');
        /* @var View $view */
        /* @var User $user */

        $user->getSettings()->setView($view);

        // TODO: Detect if the view has statuses. If not, don't request them. Instead assign status to `false`.
        $status = false;
        /** @var Zulu $zulu */
        if ($zulu = $em->getRepository('AppBundle:Zulu')->findOneBy(['lockedBy' => $user->getUsername()])) {
            $commandHandler = $this->get('command_handler');
            $commandHandler->setZulu($zulu);
            $status = $commandHandler->getStatusOfZulu();
        }

        return $this->render(
            '@App/app/content/controller.html.twig', [
                'user'    => $user,
                'observe' => $observing,
                'status'  => $status,
                'update'  => true,
            ]
        );
    }

    /**
     * This route **serves** the page that is responsible for changing the settings of the application and **handles**
     * the input that is sent by the client. It uses the full capability of the `SettingsHandler.php` and devides the
     * LDAP-settings from the other settings.
     *
     * @Route("/settings", name="admin_settings_route")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function settingsAction(Request $request): Response
    {
        $settingsHandler = $this->get('app.settings_handler');
        $settings        = $settingsHandler->getSettings();

        if ($request->get('senden')) {
            if ($values = $request->request) {
                foreach ($values as $name => $value) {
                    if (key_exists($name, $settings)) {
                        $settings[$name] = $value;
                    }
                }
                $settingsHandler->setSettings($settings);
            }
        }

        $filesystem = $this->get('filesystem');
        $realCacheDir = $this->getParameter('kernel.cache_dir');
        $this->get('cache_clearer')->clear($realCacheDir);
        $filesystem->remove($realCacheDir);

        return $this->render('AppBundle:admin:settings.html.twig', ['settings' => $settings]);
    }

    /**
     * This route serves the page for checking the statuses of the zulus. The form is generated in the
     * `status.html.twig` templating file.
     *
     * @Route("/status", name="admin_status_route")
     *
     * @return Response
     */
    public function statusAction(): Response
    {
        /** @var Zulu[] $zulus */
        $buildings = $this->get('doctrine.orm.entity_manager')->getRepository('AppBundle:Building')->findAll();

        // TODO: Track user and set $building to his building.
        return $this->render('@App/admin/status.html.twig', ['buildings' => $buildings]);
    }

    /**
     * This route acts as an API for the status route. It fetches the statuses for the zulus and returns the output.
     *
     * @Route(
     *     "/status/{building}",
     *     name="admin_status_api_route",
     *     options={"expose": true}
     * )
     * @Route("/status/all")
     * @ParamConverter("building", class="AppBundle:Building", isOptional="true",
     *     options={"mapping": {"building" = "name"}})
     *
     * @param Request  $request
     * @param Building $building
     *
     * @return Response
     */
    public function statusApiAction(Request $request, $building = null): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('admin_status_route');
        }

        $em = $this->get('doctrine.orm.entity_manager');
        if ($building === null) {
            $zulus = $em->getRepository('AppBundle:Zulu')->findAll();
        } else {
            $rooms = $em->getRepository('AppBundle:Room')->findBy(['building' => $building]);
            $zulus = [];
            foreach ($rooms as $room) {
                $zulus[] = $room->getZulu();
            }
        }
        $statuses = $this->get('app.status.fetcher')->fetch($zulus);

        return $this->render('@App/admin/snippets/statuses.html.twig', ['zulus' => $zulus, 'statuses' => $statuses]);
    }

    /**
     * @Route("/infrastructure", name="admin_infrastructure_route", options={"expose": true})
     *
     * @return Response
     */
    public function infrastructureAction(): Response
    {
        // TODO: Interface for managing the infrastructure. Maybe extract to own Controller.
        return new Response("Hello");
    }

    /**
     * This route handles the filter, the it or hw sets when looking for a specific set of log entries.
     * At it's current state it allows be filtert through **the user** that is associated with a log and the **log
     * level** the log entry is weighted.
     *
     * @Route("/logs", name="admin_filter_logs_route", methods={"POST"}, options={"expose": true})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function filterLogsAction(Request $request): Response
    {
        $values = $request->request;
        $em     = $this->get('doctrine.orm.entity_manager');
        if ($values->get('filtern')) {
            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder->select('l')->from('AppBundle:Log', 'l')->leftJoin('l.user', 'u')->setMaxResults(100);

            if ($values->get('benutzer') !== '') {
                $queryBuilder->andWhere('u.username = :username')->setParameter(':username', $values->get('benutzer'));
            }

            if ($values->get('level')) {
                $queryBuilder->andWhere('l.level = :level')->setParameter(':level', $values->get('level'));
            }

            $logs = $queryBuilder->orderBy('l.dateTime', 'DESC')->getQuery()->getResult();
        } else {
            $logs = $em->getRepository('AppBundle:Log')->findAll();
        }

        return $this->render('AppBundle:admin/snippets:logTable.html.twig', ['logs' => $logs]);
    }

    /**
     * This route serves the log page. You can search and filter through logentries.
     *
     * @Route("/logs", name="admin_logs_route")
     *
     * @return Response
     */
    public function logsAction(): Response
    {
        $em    = $this->get('doctrine.orm.entity_manager');
        $users = $em->getRepository('AppBundle:User')->findAll();

        $date = new \DateTime('-5 day');
        $logs = $em->createQuery(
            'SELECT l FROM AppBundle:Log l WHERE l.dateTime >= :datetime ORDER BY l.dateTime DESC'
        )->setMaxResults(100)->setParameter('datetime', $date)->getResult();

        return $this->render('@App/admin/logs.html.twig', ['logs' => $logs, 'users' => $users]);
    }
}
