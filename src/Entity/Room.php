<?php

namespace App\Entity;

use App\Entity\Traits\Id;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Class Room.
 *
 * This entity represents the room inside a building that will be accessible through this application.
 *
 * @ORM\Entity()
 */
class Room implements JsonSerializable
{
    use Id;

    /**
     * The name of the room. For example: "EG 1.4", "AU2", "Floor 1: Room 4".
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * The zulu that is inside this room.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Zulu", mappedBy="room", cascade={"persist"})
     */
    private $zulu;

    /**
     * The atlona that is inside this room.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Atlona", mappedBy="room", cascade={"persist"})
     */
    private $atlona;

    /**
     * The computer inside this room.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Computer", mappedBy="room", cascade={"persist"})
     */
    private $computer;

    /**
     * The building that this room is in.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Building", inversedBy="rooms")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id")
     */
    private $building;

    /**
     * Room constructor.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function setZulu(Zulu $zulu): void
    {
        $this->zulu = $zulu;
        $zulu->setRoom($this);
    }

    public function getZulu(): ?Zulu
    {
        return $this->zulu;
    }

    public function getComputer(): ?Computer
    {
        return $this->computer;
    }

    public function setComputer(Computer $computer): void
    {
        $this->computer = $computer;
        $computer->setRoom($this);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setBuilding(Building $building): void
    {
        $this->building = $building;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function getAtlona(): ?Atlona
    {
        return $this->atlona;
    }

    public function setAtlona(Atlona $atlona): void
    {
        $this->atlona = $atlona;
    }

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
