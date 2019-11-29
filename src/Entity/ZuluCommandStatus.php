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
     * @var int
     */
    private $id;

    /**
     * Whether the state of the controller is on or off.
     *
     * @ORM\Column(name="state", type="boolean")
     *
     * @var bool
     */
    private $on;

    /**
     * The state that hold all `ZuluCommandStatus`.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ZuluStatus", inversedBy="commandStatuses")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     *
     * @var ZuluStatus
     */
    private $zuluStatus;

    /**
     * The command that is linked with this entity.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ZuluCommand")
     * @ORM\JoinColumn(name="command_id", referencedColumnName="id")
     *
     * @var ZuluCommand
     */
    private $command;

    /**
     * ZuluCommandStatus constructor.
     */
    public function __construct(ZuluCommand $command, bool $on)
    {
        $this->on      = $on;
        $this->command = $command;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function isOn(): bool
    {
        return $this->on;
    }

    public function getZuluStatus(): ZuluStatus
    {
        return $this->zuluStatus;
    }

    public function setZuluStatus(ZuluStatus $zuluStatus): void
    {
        $this->zuluStatus = $zuluStatus;
    }

    public function getCommand(): ZuluCommand
    {
        return $this->command;
    }
}
