<?php

namespace App\EventListener;

use App\Entity\UserSettings;
use App\Entity\View;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class UserSettingsListener.
 *
 * This EventListener listens on the `UserSettings`.
 */
class UserSettingsListener
{
    /**
     * This method runs when the `UserSettings` are persisted.
     *
     * When the settings are created, set the default view. Because otherwise it would be empty.
     * This code will be potentially removed in the future.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof UserSettings) {
            $em       = $args->getEntityManager();
            $settings = $entity;

            if ($settings->getId() === null && $settings->getView() === null) {
                // TODO: Make the default view configurable.
                /** @var View $view */
                $view = $em->getRepository(View::class)->findOneBy(['name' => 'Cockpit']);
                $settings->setView($view);
            }
        }
    }
}
