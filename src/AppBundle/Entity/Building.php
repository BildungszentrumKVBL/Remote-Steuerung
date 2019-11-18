<?php

namespace AppBundle\Entity;

use JsonSerializable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
     * @var int $id
     */
    private $id;

    /**
     * The name to identify the building. Such as A, B, C ... or 2C.
     *
     * @ORM\Column(name="name", type="string", length=25)
     *
     * @var string $name
     */
    private $name;

    /**
     * The rooms that exists in this building.
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Room", mappedBy="building", cascade={"persist"})
     *
     * @var Collection $rooms
     */
    private $rooms;

    /**
     * Building constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name  = $name;
        $this->rooms = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Room $room
     *
     * @return $this
     */
    public function addRoom(Room $room)
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setBuilding($this);
        }

        return $this;
    }

    /**
     * @param Room $room
     */
    public function removeRoom(Room $room)
    {
        $this->rooms->removeElement($room);
    }

    /**
     * @return Collection
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    /**
     * This function is a so called `magic function`. Their purpose is very functional.
     *
     * This `__toString()`-function will be called if the object will be casted into a string.
     *
     * For instance when trying to use `print()` or `echo` on this object.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
