<?php

namespace AppBundle\Entity;

use JsonSerializable;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Zulu.
 *
 * This entity represents the Zulu that is active inside a room.
 *
 * @see [Neets-Control ZuLu](http://www.neets.dk/products/av-control-systems/83/310-0050)
 *
 * @ORM\Entity()
 * @ORM\Table(name="zulu")
 * @UniqueEntity("ip")
 * @UniqueEntity("room")
 * @UniqueEntity("lookedBy")
 */
class Zulu implements JsonSerializable
{
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
     * This is the flag that tells if the zulu is active or not.
     *
     * @todo: Can be used later for implementing a maintenance mode.
     *
     * @ORM\Column(name="active", type="boolean")
     *
     * @var bool $active
     */
    private $active;

    /**
     * The actual IP of the Zulu.
     *
     * @ORM\Column(name="ip", type="string")
     *
     * @var string $ip
     */
    private $ip;

    /**
     * The room the Zulu lives in.
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Room", inversedBy="zulu")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     *
     * @var Room $room
     */
    private $room;

    /**
     * The flag whether the the Zulu is locked or not. This will be removed in a future version.
     *
     * @ORM\Column(name="locked", type="boolean")
     *
     * @var bool $locked
     */
    private $locked;

    /**
     * Past statuses of the Zulu.
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ZuluStatus", mappedBy="zulu", cascade={"all"})
     *
     * @var ArrayCollection $statuses
     */
    private $statuses;

    /**
     * If the Zulu is locked, the username will be set in this field. This will be removed in a future version.
     *
     * @ORM\Column(name="locked_by", type="string", nullable=true)
     *
     * @var string $lockedBy
     */
    private $lockedBy;

    /**
     * When the Zulu gets locked, the time of the event will be set into this field.
     *
     * @ORM\Column(name="locked_since", type="datetime", nullable=true)
     *
     * @var \DateTime $lockedSince
     */
    private $lockedSince;

    /**
     * The part of the URL that is needed for the status.
     *
     * @var string $statusUri
     */
    private $statusUri = '/elements.xml';

    /**
     * Zulu constructor.
     *
     * @param string $ip
     */
    public function __construct(string $ip)
    {
        $this->ip       = $ip;
        $this->active   = true;
        $this->locked   = false;
        $this->lockedBy = null;
        $this->statuses = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param Room $room
     */
    public function setRoom(Room $room)
    {
        $this->room = $room;
    }

    /**
     * @return Room
     */
    public function getRoom(): Room
    {
        return $this->room;
    }

    /**
     * @return boolean
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @return string
     */
    public function getLockedBy()
    {
        return $this->lockedBy;
    }

    /**
     * @return \DateTime
     */
    public function getLockedSince()
    {
        return $this->lockedSince;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    /**
     * @param ZuluStatus $status
     */
    public function addStatus(ZuluStatus $status)
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
            $status->setZulu($this);
        }
    }

    /**
     * @param ZuluStatus $status
     */
    public function removeStatus(ZuluStatus $status)
    {
        $this->statuses->removeElement($status);
    }

    /**
     * Locks the Zulu to prevent other users from using it.
     *
     * @param User $user
     */
    public function lock(User $user)
    {
        $this->locked      = true;
        $this->lockedBy    = $user->getUsername();
        $this->lockedSince = new DateTime();
    }

    /**
     * Unlocks Zulu for other users.
     */
    public function unlock()
    {
        $this->locked      = false;
        $this->lockedBy    = null;
        $this->lockedSince = null;
    }

    /**
     * @return string
     */
    public function getStatusUrl(): string
    {
        return $this->ip.$this->statusUri;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array(
            'id'       => $this->id,
            'active'   => $this->active,
            'room'     => $this->room->jsonSerialize(),
            'locked'   => $this->locked,
            'building' => $this->room->getBuilding()->jsonSerialize(),
        );
    }
}
