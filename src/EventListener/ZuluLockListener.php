<?php

namespace App\EventListener;

use App\Entity\Zulu;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class ZuluLockListener.
 *
 * This EventListener listens when the Zulu
 */
class ZuluLockListener
{
    /**
     * The pusher that publishes the command to the subscribed user on the observation-page.
     *
     * @var PusherInterface $pusher
     */
    private $pusher;

    /**
     * The whole DependencyInjectionContainer.
     * This is a very bad case... We have to load the whole container because otherwise,
     * it would create a CircularReferenceError.
     *
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * ObservationListener constructor.
     *
     * @param PusherInterface    $pusher
     * @param ContainerInterface $container
     */
    public function __construct(PusherInterface $pusher, ContainerInterface $container)
    {
        $this->container = $container;
        $this->pusher    = $pusher;
        $this->update    = false;
    }

    /**
     * When a Zulu gets persisted, set a flag to run at the end of the request.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        if ($args->getEntity() instanceof Zulu) {
            $this->update = true;
        }
    }

    /**
     * If the flag is set from the `postUpdate`-function, notify the observers about all currently locked Zulus.
     *
     * @todo: This application-flow will be optimized in the future.
     */
    public function onKernelResponse()
    {
        if ($this->update) {
            $this->update = false;
            $zulus        = $this->container->get('doctrine.orm.entity_manager')->getRepository(Zulu::class)->findBy(['locked' => true]);
            // Added this workaround to not call a CircularReferenceException due to the bidirectional One-to-One relationship between the Zulu and Room Entity.
            $dataArray = [];
            foreach ($zulus as $zulu) {
                /* @var Zulu $zulu */
                $dataArray[] = $zulu->jsonSerialize();
            }
            $this->pusher->push(
                [
                    'action' => 'updateZulus',
                    'data'   => $dataArray,
                ], 'command_topic'
            );
        }
    }
}
