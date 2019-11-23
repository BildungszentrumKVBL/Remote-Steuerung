<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ZuluCommandStatus.
 *
 * The status of an individual ZuluCommand at a specific time.
 *
 * @ORM\Entity()
 * @ORM\Table(name="zulu_command_status")
 */
class ZuluCommandStatus
{
    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int $id
     */
    private $id;

    /**
     * Whether the state of the controller is on or off.
     *
     * @ORM\Column(name="state", type="boolean")
     *
     * @var bool $on
     */
    private $on;

    /**
     * The state that hold all `ZuluCommandStatus`.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ZuluStatus", inversedBy="commandStatuses")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     *
     * @var ZuluStatus $zuluStatus
     */
    private $zuluStatus;

    /**
     * The command that is linked with this entity.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ZuluCommand")
     * @ORM\JoinColumn(name="command_id", referencedColumnName="id")
     *
     * @var ZuluCommand $command
     */
    private $command;

    /**
     * ZuluCommandStatus constructor.
     *
     * @param ZuluCommand $command
     * @param bool        $on
     */
    public function __construct(ZuluCommand $command, bool $on)
    {
        $this->on      = $on;
        $this->command = $command;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isOn(): bool
    {
        return $this->on;
    }

    /**
     * @return ZuluStatus
     */
    public function getZuluStatus(): ZuluStatus
    {
        return $this->zuluStatus;
    }

    /**
     * @param ZuluStatus $zuluStatus
     */
    public function setZuluStatus(ZuluStatus $zuluStatus)
    {
        $this->zuluStatus = $zuluStatus;
    }

    /**
     * @return ZuluCommand
     */
    public function getCommand(): ZuluCommand
    {
        return $this->command;
    }
}
