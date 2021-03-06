<?php

namespace AppBundle\Entity;

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
     * @var int $id
     */
    private $id;

    /**
     * The time and date of the captures state of the statuses.
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @var \DateTime $createdAt
     */
    private $createdAt;

    /**
     * The statuses of the individual commands.
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ZuluCommandStatus", mappedBy="zuluStatus", cascade={"all"})
     *
     * @var Collection $commandStatuses
     */
    private $commandStatuses;

    /**
     * The Zulu associated with the status.
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Zulu", inversedBy="statuses")
     * @ORM\JoinColumn(name="zulu_id", referencedColumnName="id")
     *
     * @var Zulu $zulu
     */
    private $zulu;

    /**
     * ZuluStatus constructor.
     */
    public function __construct()
    {
        $this->createdAt       = new \DateTime();
        $this->commandStatuses = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param ZuluCommandStatus $status
     */
    public function addCommandStatus(ZuluCommandStatus $status)
    {
        if (!$this->commandStatuses->contains($status)) {
            $status->setZuluStatus($this);
            $this->commandStatuses->add($status);
        }
    }

    /**
     * @param ZuluCommandStatus $status
     */
    public function removeCommandStatus(ZuluCommandStatus $status)
    {
        $this->commandStatuses->removeElement($status);
    }

    /**
     * @return Collection
     */
    public function getCommandStatuses(): Collection
    {
        return $this->commandStatuses;
    }

    /**
     * @return Zulu
     */
    public function getZulu()
    {
        return $this->zulu;
    }

    /**
     * @param Zulu $zulu
     */
    public function setZulu(Zulu $zulu)
    {
        $this->zulu = $zulu;
    }

}
