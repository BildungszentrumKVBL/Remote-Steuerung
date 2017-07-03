<?php

namespace AppBundle\EventDispatcher\Event;


use AppBundle\Entity\User;
use AppBundle\Entity\View;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ChangedSettingsEvent.
 *
 * This class is used as a model for event handling.
 *
 * @IPA
 */
class ChangedSettingsEvent extends Event
{
    /**
     * @var View $view
     *
     * @IPA
     */
    private $oldView;

    /**
     * @var View $newView
     *
     * @IPA
     */
    private $newView;

    /**
     * @var User $user
     *
     * @IPA
     */
    private $user;

    /**
     * @IPA
     *
     * @return View
     */
    public function getOldView(): View
    {
        return $this->oldView;
    }

    /**
     * The old view will be clone to remove the reference to the object.
     *
     * @IPA
     *
     * @param View $oldView
     */
    public function setOldView(View $oldView)
    {
        $this->oldView = clone $oldView;
    }

    /**
     * @IPA
     *
     * @return View
     */
    public function getNewView(): View
    {
        return $this->newView;
    }

    /**
     * @IPA
     *
     * @param View $newView
     */
    public function setNewView(View $newView)
    {
        $this->newView = $newView;
    }

    /**
     * @IPA
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @IPA
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
