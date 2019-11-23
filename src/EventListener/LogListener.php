<?php

namespace App\EventListener;

use App\Entity\Log;
use App\Service\SettingsHandler;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LogListener.
 *
 * This EventListener listens on when a log is being created and saved in the database.
 *
 * When the log that wants to be created has a lower level than the configured log level, the log will not be saved.
 */
class LogListener
{
    /**
     * The configured minimal level of the logs.
     *
     * @var int $minLevel
     */
    private $minLevel;

    /**
     * LogListener constructor.
     *
     * @param int $minLevel
     */
    public function __construct(int $minLevel)
    {
        $this->minLevel = $minLevel;
    }

    /**
     * This is the function that will be triggered and executed when an entity is persisted.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Log) {
            return;
        }

        if ($entity->getLevel() < $this->minLevel) {
            $args->getEntityManager()->remove($entity);
        }
    }
}
