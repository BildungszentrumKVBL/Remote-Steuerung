<?php

namespace AppBundle\EventDispatcher\Event;


use AppBundle\Entity\User;
use AppBundle\Entity\View;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ChangedSettingsEvent.
 *
 * This class is used as a model for event handling.
 */
class ChangedSettingsEvent extends Event
{
    /**
     * @var View $view
     */
    private $oldView;

    /**
     * @var View $newView
     */
    private $newView;

    /**
     * @var User $user
     */
    private $user;

    /**
     * @return View
     */
    public function getOldView(): View
    {
        return $this->oldView;
    }

    /**
     * The old view will be clone to remove the reference to the object.
     *
     * @param View $oldView
     */
    public function setOldView(View $oldView)
    {
        $this->oldView = clone $oldView;
    }

    /**
     * @return View
     */
    public function getNewView(): View
    {
        return $this->newView;
    }

    /**
     * @param View $newView
     */
    public function setNewView(View $newView)
    {
        $this->newView = $newView;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
