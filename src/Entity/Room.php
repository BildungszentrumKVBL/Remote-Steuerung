<?php

namespace App\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Room.
 *
 * This entity represents the room inside a building that will be accessible through this application.
 *
 * @ORM\Entity()
 */
class Room implements JsonSerializable
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
     * The name of the room. For example: "EG 1.4", "AU2", "Floor 1: Room 4".
     *
     * @ORM\Column(name="name", type="string")
     *
     * @var string $name
     */
    private $name;

    /**
     * The zulu that is inside this room.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Zulu", mappedBy="room", cascade={"persist"})
     *
     * @var Zulu $zulu
     */
    private $zulu;

    /**
     * The computer inside this room.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Computer", mappedBy="room", cascade={"persist"})
     *
     * @var Computer $computer
     */
    private $computer;

    /**
     * The building that this room is in.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Building", inversedBy="rooms")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id")
     *
     * @var Building $building
     */
    private $building;

    /**
     * Room constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param Zulu $zulu
     */
    public function setZulu(Zulu $zulu)
    {
        $this->zulu = $zulu;
        $zulu->setRoom($this);
    }

    /**
     * @return Zulu
     */
    public function getZulu()
    {
        return $this->zulu;
    }

    /**
     * @return Computer
     */
    public function getComputer()
    {
        return $this->computer;
    }

    /**
     * @param Computer $computer
     */
    public function setComputer(Computer $computer)
    {
        $this->computer = $computer;
        $computer->setRoom($this);
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
     * @param Building $building
     */
    public function setBuilding(Building $building)
    {
        $this->building = $building;
    }

    /**
     * @return Building
     */
    public function getBuilding()
    {
        return $this->building;
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
