<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Monolog\Logger;

/**
 * Class UserEventListener.
 *
 * This EventListener listens to Events from the `User`-entity.
 */
class UserEventListener
{
    /**
     * The logger of the web-application.
     *
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var  $groupIt
     */
    private $groupIt;

    /**
     * @var string $groupCaretaker
     */
    private $groupCaretaker;

    /**
     * @var  $groupTeacher
     */
    private $groupTeacher;

    /**
     * @var  $groupItTeacher
     */
    private $groupItTeacher;

    /**
     * UserEventListener constructor.
     *
     * @param string $groupIt
     * @param string $groupCaretaker
     * @param string $groupTeacher
     * @param string $groupItTeacher
     */
    public function __construct(string $groupIt, string $groupCaretaker, string $groupTeacher, string $groupItTeacher)
    {
        $this->groupIt        = $groupIt;
        $this->groupCaretaker = $groupCaretaker;
        $this->groupTeacher   = $groupTeacher;
        $this->groupItTeacher = $groupItTeacher;
    }

    /**
     * This method runs when an user gets persistet.
     *
     * It checks if the user is being created, and if he is, takes the LDAP-groups from him and sets the corresponding
     * groups in the web-application.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $user = $args->getEntity();

        if (!$user instanceof User) {
            return;
        }


        // Checks the user is new.
        if ($user->getId() === null) {
            $message = 'Usergroups are: ';
            foreach ($user->getLdapGroups() as $group) {
                $message .= $group.', ';
            }
            $this->logger->info($message);

            $mapping = [
                'IT'         => $this->groupIt,
                'HW'         => $this->groupCaretaker,
                'IT-Teacher' => $this->groupItTeacher,
                'Teacher'    => $this->groupTeacher,
            ];

            foreach ($mapping as $group => $ou) {
                $this->addGroupByOU($args, $group, $ou);
            }
        }
    }

    private function addGroupByOU(LifecycleEventArgs $args, string $ou, string $groupName)
    {
        $em = $args->getEntityManager();
        /** @var User $user */
        $user = $args->getEntity();
        if (in_array($ou, $user->getLdapGroups())) {
            /** @var Group $group */
            $group = $em->getRepository('AppBundle:Group')->findOneBy(array('name' => $groupName));
            if ($group) {
                $user->addGroup($group);
            }
        }
    }

    /**
     * This function is used for a setter-DependencyInjection.
     *
     * @see [Setter-DependencyInjection](http://symfony.com/doc/current/service_container/injection_types.html#setter-injection)
     *
     * @param Logger|null $logger
     */
    public function setLogger(Logger $logger = null)
    {
        $this->logger = $logger;
    }
}
