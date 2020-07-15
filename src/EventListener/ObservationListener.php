<?php

namespace App\EventListener;

use App\EventDispatcher\Event\CommandEvent;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;

/**
 * Class ObservationListener.
 *
 * This EventListener listens on the `CommandEvent`.
 */
class ObservationListener
{
    /**
     * The pusher that publishes the command to the subscribed user on the observation-page.
     *
     * @var PusherInterface
     */
    private $pusher;

    /**
     * ObservationListener constructor.
     */
    public function __construct(PusherInterface $pusher)
    {
        $this->pusher = $pusher;
    }

    /**
     * When the event is triggered, this method runs.
     *
     * It sends data to the users on the observation-page.
     *
     * The data contain the action that the web-client has to make and the data for this action.
     * The id of the user will be passed as well to differentiate between multiple observed controls.
     * The current status is also being passed to the web-client in order to update the observed controls.
     */
    public function onCommandEvent(CommandEvent $event)
    {
        $this->pusher->push(
            [
                'action' => 'moveMouse',
                'data'   => [
                    'id'      => $event->getUser()->getId(),
                    'command' => $event->getCommand()->getName(),
                    'status'  => $event->getStatus(),
                ],
            ], 'command_topic'
        );
    }
}
