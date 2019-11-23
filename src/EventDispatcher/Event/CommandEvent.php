<?php

namespace App\EventDispatcher\Event;

use App\Entity\AbstractCommand;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CommandEvent.
 *
 * This class is used as a model for event handling.
 */
class CommandEvent extends Event
{
    /**
     * The user that triggers the command.
     *
     * @var User $user
     */
    private $user;

    /**
     * The command that is triggered.
     *
     * @var AbstractCommand $command
     */
    private $command;

    /**
     * @var array $status
     */
    private $status;

    /**
     * @return AbstractCommand
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param AbstractCommand $command
     *
     * @return $this
     */
    public function setCommand(AbstractCommand $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param array $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
