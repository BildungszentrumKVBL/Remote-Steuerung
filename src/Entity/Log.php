<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Class Log.
 *
 * This class functions as a sortable and filterable log.
 *
 * It has multiple levels, which can be configured.
 *
 * @ORM\Entity()
 * @ORM\Table(name="log")
 */
class Log implements JsonSerializable
{
    /**
     * Basic informational logs.
     */
    public const LEVEL_INFO = 1;

    /**
     * Logs that are tied to a command.
     */
    public const LEVEL_COMMAND = 2;

    /**
     * This level implies that the application logged something about itself.
     */
    public const LEVEL_SYSTEM = 3;

    /**
     * General errorlogs.
     */
    public const LEVEL_ERROR = 4;

    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * The level of the log. This describes the urgency of the logentry.
     *
     * @ORM\Column(name="level", type="integer")
     *
     * @var int
     */
    private $level;

    /**
     * This is the message that is contained in the log.
     *
     * @ORM\Column(name="message", type="string")
     *
     * @var string
     */
    private $message;

    /**
     * The time when the log was generated.
     *
     * @ORM\Column(name="date_time", type="datetime")
     *
     * @var \DateTime
     */
    private $dateTime;

    /**
     * The user that is responsible for the log.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     *
     * @var User
     */
    private $user;

    /**
     * Log constructor.
     *
     * @param User $user
     */
    public function __construct(string $message, int $level, User $user = null)
    {
        $this->message  = $message;
        $this->level    = $level;
        $this->user     = $user;
        $this->dateTime = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'      => $this->id,
            'level'   => $this->level,
            'message' => $this->message,
            'user'    => $this->user,
        ];
    }
}
