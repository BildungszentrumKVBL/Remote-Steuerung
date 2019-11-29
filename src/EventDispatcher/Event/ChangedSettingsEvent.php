<?php

namespace App\EventDispatcher\Event;

use App\Entity\User;
use App\Entity\View;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ChangedSettingsEvent.
 *
 * This class is used as a model for event handling.
 */
class ChangedSettingsEvent extends Event
{
    /**
     * @var View
     */
    private $oldView;

    /**
     * @var View
     */
    private $newView;

    /**
     * @var User
     */
    private $user;

    public function getOldView(): View
    {
        return $this->oldView;
    }

    /**
     * The old view will be clone to remove the reference to the object.
     */
    public function setOldView(View $oldView)
    {
        $this->oldView = clone $oldView;
    }

    public function getNewView(): View
    {
        return $this->newView;
    }

    public function setNewView(View $newView)
    {
        $this->newView = $newView;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
