<?php

namespace App\EventDispatcher\Event;

use App\Entity\AbstractCommand;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

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
     * @var User
     */
    private $user;

    /**
     * The command that is triggered.
     *
     * @var AbstractCommand
     */
    private $command;

    /**
     * @var array
     */
    private $status;

    /**
     * @return AbstractCommand
     */
    public function getCommand(): ?AbstractCommand
    {
        return $this->command;
    }

    /**
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
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
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
