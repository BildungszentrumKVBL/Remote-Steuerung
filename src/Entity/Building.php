<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Class Building.
 *
 * This entity represents the buildings of the company that will use this application.
 *
 * @todo: When there is only one building. Skip selections of buildings.
 *
 * @ORM\Entity()
 */
class Building implements JsonSerializable
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
     * The name to identify the building. Such as A, B, C ... or 2C.
     *
     * @ORM\Column(name="name", type="string", length=25)
     *
     * @var string
     */
    private $name;

    /**
     * The rooms that exists in this building.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Room", mappedBy="building", cascade={"persist"})
     *
     * @var Collection
     */
    private $rooms;

    /**
     * Building constructor.
     */
    public function __construct(string $name)
    {
        $this->name  = $name;
        $this->rooms = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function addRoom(Room $room): void
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setBuilding($this);
        }
    }

    public function removeRoom(Room $room): void
    {
        $this->rooms->removeElement($room);
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
