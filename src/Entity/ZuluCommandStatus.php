<?php

namespace App\Entity;

use App\Entity\Traits\Id;
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
    use Id;

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
