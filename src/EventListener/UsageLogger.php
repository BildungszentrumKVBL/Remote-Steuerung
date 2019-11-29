<?php

namespace App\EventListener;

use App\Entity\Log;
use App\Entity\User;
use App\EventDispatcher\Event\CommandEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

/**
 * Class UsageLogger.
 *
 * This EventListener logs the usage of this web-application.
 */
class UsageLogger implements EventSubscriberInterface
{
    /**
     * The EntityManager that handles database-interactions.
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Returns the subscribed Events.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    /**
     * This method runs when the authentication of an user was successful.
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $log  = new Log(sprintf('%s hat sich eingeloggt.', $user instanceof User ? $user->getUsername() : 'anon'), Log::LEVEL_INFO, $user instanceof User ? $user : null);
        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * This method runs when a command is being triggered.
     *
     * TODO: Check usage.
     */
    public function onCommandEvent(CommandEvent $event)
    {
        $log = new Log(
            sprintf(
                '%s %s hat den Befehl "%s" ausgeführt.',
                $event->getUser()->getFirstName(),
                $event->getUser()->getLastName(),
                $event->getCommand()->getName()
            ), Log::LEVEL_COMMAND, $event->getUser()
        );
        $this->em->persist($log);
        $this->em->flush($log);
    }
}
