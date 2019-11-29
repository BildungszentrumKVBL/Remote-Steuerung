<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ZuluStatus.
 *
 * This entity is a set of states of `ZuluCommand`s at a specific time.
 *
 * @ORM\Entity()
 */
class ZuluStatus
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
     * The time and date of the captures state of the statuses.
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * The statuses of the individual commands.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ZuluCommandStatus", mappedBy="zuluStatus", cascade={"all"})
     *
     * @var Collection
     */
    private $commandStatuses;

    /**
     * The Zulu associated with the status.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Zulu", inversedBy="statuses")
     * @ORM\JoinColumn(name="zulu_id", referencedColumnName="id")
     *
     * @var Zulu
     */
    private $zulu;

    /**
     * ZuluStatus constructor.
     */
    public function __construct()
    {
        $this->createdAt       = new DateTime();
        $this->commandStatuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function addCommandStatus(ZuluCommandStatus $status): void
    {
        if (!$this->commandStatuses->contains($status)) {
            $status->setZuluStatus($this);
            $this->commandStatuses->add($status);
        }
    }

    public function removeCommandStatus(ZuluCommandStatus $status): void
    {
        $this->commandStatuses->removeElement($status);
    }

    public function getCommandStatuses(): Collection
    {
        return $this->commandStatuses;
    }

    public function getZulu(): ?Zulu
    {
        return $this->zulu;
    }

    public function setZulu(Zulu $zulu): void
    {
        $this->zulu = $zulu;
    }
}
