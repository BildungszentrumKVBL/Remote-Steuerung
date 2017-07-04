<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
class Log implements \JsonSerializable
{
    /**
     * Basic informational logs.
     */
    const LEVEL_INFO = 1;

    /**
     * Logs that are tied to a command.
     */
    const LEVEL_COMMAND = 2;

    /**
     * This level implies that the application logged something about itself.
     */
    const LEVEL_SYSTEM = 3;

    /**
     * General errorlogs.
     */
    const LEVEL_ERROR = 4;

    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     *
     * @var int $id
     */
    private $id;

    /**
     * The level of the log. This describes the urgency of the logentry.
     *
     * @ORM\Column(name="level", type="integer")
     *
     * @var int $level
     */
    private $level;

    /**
     * This is the message that is contained in the log.
     *
     * @ORM\Column(name="message", type="string")
     *
     * @var string $message
     */
    private $message;

    /**
     * The time when the log was generated.
     *
     * @ORM\Column(name="date_time", type="datetime")
     *
     * @var \DateTime $dateTime
     */
    private $dateTime;

    /**
     * The user that is responsible for the log.
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     *
     * @var User $user
     */
    private $user;

    /**
     * Log constructor.
     *
     * @param string $message
     * @param int    $level
     * @param User   $user
     */
    public function __construct(string $message, int $level, User $user = null)
    {
        $this->message  = $message;
        $this->level    = $level;
        $this->user     = $user;
        $this->dateTime = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    /**
     * @param \DateTime $dateTime
     */
    public function setDateTime(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array(
            'id'      => $this->id,
            'level'   => $this->level,
            'message' => $this->message,
            'user'    => $this->user
        );
    }
}
