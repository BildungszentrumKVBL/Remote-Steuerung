<?php

namespace App\EventListener;

use App\Entity\Log;
use App\EventDispatcher\Event\CommandEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class UsageLogger.
 *
 * This EventListener logs the usage of this web-application.
 */
class UsageLogger implements EventSubscriberInterface
{
    /**
     * The EntityManager that handles database-interactions.
     *
     * @var EntityManager $em
     */
    private $em;

    /**
     * UsageLogger constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns the subscribed Events.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    /**
     * This method runs when the authentication of an user was successful.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onAuthenticationSuccess(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $log  = new Log(sprintf('%s hat sich eingeloggt.', $user->getUsername()), Log::LEVEL_INFO, $user);
        $this->em->persist($log);
        $this->em->flush($log);
    }

    /**
     * This method runs when a command is being triggered.
     *
     * @param CommandEvent $event
     */
    public function onCommandEvent(CommandEvent $event)
    {
        $log = new Log(
            sprintf(
                '%s %s hat den Befehl "%s" ausgeführt.',
                $event->getUser()->getFirstName(),
                $event->getUser()->getLastName(),
                $event->getCommand()->getName()
            )
            , Log::LEVEL_COMMAND, $event->getUser()
        );
        $this->em->persist($log);
        $this->em->flush($log);
    }
}
