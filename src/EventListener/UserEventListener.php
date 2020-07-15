<?php

namespace App\EventListener;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class UserEventListener.
 *
 * This EventListener listens to Events from the `User`-entity.
 */
class UserEventListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var
     */
    private $groupIt;

    /**
     * @var string
     */
    private $groupCaretaker;

    /**
     * @var
     */
    private $groupTeacher;

    /**
     * @var
     */
    private $groupItTeacher;

    /**
     * UserEventListener constructor.
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
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $user = $args->getEntity();
        if ($user instanceof User) {
            $em = $args->getEntityManager();
            // Checks the user is new.
            if (null === $user->getId()) {
                $message = 'Usergroups are: ';
                foreach ($user->getLdapGroups() as $group) {
                    $message .= $group.', ';
                }
                if ($this->logger) {
                    $this->logger->info($message);
                }
                $repo = $em->getRepository(Group::class);
                if (in_array($this->groupIt, $user->getLdapGroups())) {
                    $group = $repo->findOneBy(['name' => 'IT']);
                    /* @var Group $group */
                    $user->addGroup($group);
                } elseif (in_array($this->groupCaretaker, $user->getLdapGroups())) {
                    $group = $repo->findOneBy(['name' => 'HW']);
                    /* @var Group $group */
                    $user->addGroup($group);
                } elseif (in_array($this->groupItTeacher, $user->getLdapGroups())) {
                    $group = $repo->findOneBy(['name' => 'IT-Teacher']);
                    /* @var Group $group */
                    $user->addGroup($group);
                } elseif (in_array($this->groupTeacher, $user->getLdapGroups())) {
                    $group = $repo->findOneBy(['name' => 'Teacher']);
                    /* @var Group $group */
                    $user->addGroup($group);
                }
            }
        }
    }
}
