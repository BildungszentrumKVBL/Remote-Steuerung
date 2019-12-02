<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\Log;
use App\Entity\Room;
use App\Entity\User;
use App\Entity\View;
use App\Entity\Zulu;
use App\Service\CommandsHandler;
use App\Service\SettingsHandler;
use App\Service\StatusFetcher;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController.
 *
 * This controller serves all pages that are accessible for the it and hw. For more details see the firewall section in
 * the`./app/config/security.yml`.
 *
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * Serves the observation page for the administrators.
     * It gathers all zulus that are locked and passes them with their status to the `observe.html.twig`.
     *
     * @Route("/observe", name="admin_observe_route", options={"expose": true})
     */
    public function observeAction(Request $request, EntityManagerInterface $em, CommandsHandler $handler): Response
    {
        /** @var Zulu[] $lockedZulus */
        $lockedZulus = $em->getRepository(Zulu::class)->findBy(['locked' => true], ['room' => 'ASC']);
        $activeUsers = [];
        foreach ($lockedZulus as $lockedZulu) {
            $user          = $em->getRepository(User::class)->findOneBy(['username' => $lockedZulu->getLockedBy()]);
            $handler->setZulu($lockedZulu);
            $activeUsers[] = [
                'user'   => $user,
                'zulu'   => $lockedZulu,
                'status' => $handler->getStatusOfZulu(),
            ];
        }
        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/content/observe.html.twig', ['activeUsers' => $activeUsers]);
        }

        return $this->render('admin/observe.html.twig', ['activeUsers' => $activeUsers]);
    }

    /**
     * When a administrator's client is being informed about a new reservation of a zulu, it will request the zulu in
     * this route. It sends the `id` of the zulu that will join the observation-interface.
     *
     * @Route(
     *     "/observe/{id}",
     *     name="admin_observe_new_route",
     *     options={"expose": true}
     * )
     */
    public function observeNewAction(Zulu $zulu, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['username' => $zulu->getLockedBy()]);

        return $this->render('admin/snippets/observation.html.twig', ['user' => $user, 'zulu' => $zulu]);
    }

    /**
     * @Route(
     *     "/observer/update/{userId}/{viewId}",
     *     name="admin_observe_update_route",
     *     options={"expose": true},
     *     requirements={"userId": "\d+", "viewId": "\d+"}
     * )
     * @ParamConverter("user", class="App\Entity\User", options={"id" = "userId"})
     * @ParamConverter("view", class="App\Entity\View", options={"id" = "viewId"})
     */
    public function updateObservedAction(User $user, View $view, EntityManagerInterface $em, CommandsHandler $commandHandler): Response
    {
        $observing = true;
        $user->getSettings()->setView($view);

        // TODO: Detect if the view has statuses. If not, don't request them. Instead assign status to `false`.
        $status = false;
        /** @var Zulu $zulu */
        if ($zulu = $em->getRepository(Zulu::class)->findOneBy(['lockedBy' => $user->getUsername()])) {
            $commandHandler->setZulu($zulu);
            $status = $commandHandler->getStatusOfZulu();
        }

        return $this->render(
            'app/content/controller.html.twig', [
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
     */
    public function settingsAction(Request $request, SettingsHandler $settingsHandler): Response
    {
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

        return $this->render('admin/settings.html.twig', ['settings' => $settings]);
    }

    /**
     * This route serves the page for checking the statuses of the zulus. The form is generated in the
     * `status.html.twig` templating file.
     *
     * @Route("/status", name="admin_status_route")
     */
    public function statusAction(EntityManagerInterface $em): Response
    {
        /** @var Zulu[] $zulus */
        $buildings = $em->getRepository(Building::class)->findAll();

        // TODO: Track user and set $building to his building.
        return $this->render('admin/status.html.twig', ['buildings' => $buildings]);
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
     * @ParamConverter("building", class="App\Entity\Building", isOptional="true",
     *     options={"mapping": {"building" = "name"}})
     *
     * @param Building $building
     */
    public function statusApiAction(Request $request, EntityManagerInterface $em, StatusFetcher $statusFetcher, $building = null): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('admin_status_route');
        }

        if (null === $building) {
            $zulus = $em->getRepository(Zulu::class)->findAll();
        } else {
            $rooms = $em->getRepository(Room::class)->findBy(['building' => $building]);
            $zulus = [];
            foreach ($rooms as $room) {
                $zulus[] = $room->getZulu();
            }
        }
        $statuses = $statusFetcher->fetch($zulus);

        return $this->render('admin/snippets/statuses.html.twig', ['zulus' => $zulus, 'statuses' => $statuses]);
    }

    /**
     * This route handles the filter, the it or hw sets when looking for a specific set of log entries.
     * At it's current state it allows be filtert through **the user** that is associated with a log and the **log
     * level** the log entry is weighted.
     *
     * @Route("/logs", name="admin_filter_logs_route", methods={"POST"}, options={"expose": true})
     */
    public function filterLogsAction(Request $request, EntityManagerInterface $em): Response
    {
        $values = $request->request;
        if ($values->get('filtern')) {
            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder->select('l')->from(Log::class, 'l')->leftJoin('l.user', 'u')->setMaxResults(100);

            if ('' !== $values->get('benutzer')) {
                $queryBuilder->andWhere('u.username = :username')->setParameter(':username', $values->get('benutzer'));
            }

            if ($values->get('level')) {
                $queryBuilder->andWhere('l.level = :level')->setParameter(':level', $values->get('level'));
            }

            $logs = $queryBuilder->orderBy('l.dateTime', 'DESC')->getQuery()->getResult();
        } else {
            $logs = $em->getRepository(Log::class)->findAll();
        }

        return $this->render('admin/snippets/logTable.html.twig', ['logs' => $logs]);
    }

    /**
     * This route serves the log page. You can search and filter through logentries.
     *
     * @Route("/logs", name="admin_logs_route")
     */
    public function logsAction(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        $date = new DateTime('-5 day');
        $logs = $em->createQuery(
            'SELECT l FROM App\Entity\Log l WHERE l.dateTime >= :datetime ORDER BY l.dateTime DESC'
        )->setMaxResults(100)->setParameter('datetime', $date)->getResult();

        return $this->render('admin/logs.html.twig', ['logs' => $logs, 'users' => $users]);
    }
}
