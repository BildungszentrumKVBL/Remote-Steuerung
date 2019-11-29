<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Computer.
 *
 * This class represents the computer that will be controllable through the interface.
 *
 * @ORM\Entity()
 * @UniqueEntity("name")
 */
class Computer
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
     * The room where the computer lives in.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Room", inversedBy="computer")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     *
     * @var Room
     */
    private $room;

    /**
     * The hostname of the computer. This is important for identifying the machine in the network.
     *
     * This either has to be the IP or the FQDN.
     *
     * @ORM\Column(name="name", type="string")
     *
     * @var string
     */
    private $name;

    /**
     * Computer constructor.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setRoom(Room $room): void
    {
        $this->room = $room;
    }

    /**
     * @return Room
     */
    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
