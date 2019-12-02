<?php

namespace App\Entity;

use App\Entity\Traits\Id;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
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
 */
class Zulu implements JsonSerializable
{
    use Id;

    /**
     * This is the flag that tells if the zulu is active or not.
     *
     * @todo: Can be used later for implementing a maintenance mode.
     *
     * @ORM\Column(name="active", type="boolean")
     *
     * @var bool
     */
    private $active;

    /**
     * The actual IP of the Zulu.
     *
     * @ORM\Column(name="ip", type="string", unique=true)
     *
     * @var string
     */
    private $ip;

    /**
     * The room the Zulu lives in.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Room", inversedBy="zulu")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     *
     * @var Room
     */
    private $room;

    /**
     * The flag whether the the Zulu is locked or not. This will be removed in a future version.
     *
     * @ORM\Column(name="locked", type="boolean")
     *
     * @var bool
     */
    private $locked;

    /**
     * Past statuses of the Zulu.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ZuluStatus", mappedBy="zulu", cascade={"all"})
     *
     * @var ArrayCollection
     */
    private $statuses;

    /**
     * If the Zulu is locked, the username will be set in this field. This will be removed in a future version.
     *
     * @ORM\Column(name="locked_by", type="string", nullable=true)
     *
     * @var string
     */
    private $lockedBy;

    /**
     * When the Zulu gets locked, the time of the event will be set into this field.
     *
     * @ORM\Column(name="locked_since", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $lockedSince;

    /**
     * The part of the URL that is needed for the status.
     *
     * @var string
     */
    private $statusUri = '/elements.xml';

    /**
     * Zulu constructor.
     */
    public function __construct(string $ip)
    {
        $this->ip       = $ip;
        $this->active   = true;
        $this->locked   = false;
        $this->lockedBy = null;
        $this->statuses = new ArrayCollection();
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setRoom(Room $room): void
    {
        $this->room = $room;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @return string
     */
    public function getLockedBy(): ?string
    {
        return $this->lockedBy;
    }

    /**
     * @return \DateTime
     */
    public function getLockedSince(): ?\DateTime
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

    public function addStatus(ZuluStatus $status): void
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
            $status->setZulu($this);
        }
    }

    public function removeStatus(ZuluStatus $status): void
    {
        $this->statuses->removeElement($status);
    }

    /**
     * Locks the Zulu to prevent other users from using it.
     */
    public function lock(User $user): void
    {
        $this->locked      = true;
        $this->lockedBy    = $user->getUsername();
        $this->lockedSince = new DateTime();
    }

    /**
     * Unlocks Zulu for other users.
     */
    public function unlock(): void
    {
        $this->locked      = false;
        $this->lockedBy    = null;
        $this->lockedSince = null;
    }

    public function getStatusUrl(): string
    {
        return $this->ip.$this->statusUri;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'active'   => $this->active,
            'room'     => $this->room->jsonSerialize(),
            'locked'   => $this->locked,
            'building' => $this->room->getBuilding()->jsonSerialize(),
        ];
    }
}
