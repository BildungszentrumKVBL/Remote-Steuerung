<?php

namespace App\Entity;

use App\Entity\Traits\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * Class Atlona.
 */
class Atlona
{
    use Id;

    /**
     * @ORM\Column
     */
    private $ip;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Room", inversedBy="atlona")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    private $room;

    public function __construct(string $ip, Room $room)
    {
        $this->ip   = $ip;
        $this->room = $room;
        $room->setAtlona($this);
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getRoom(): string
    {
        return $this->room;
    }
}
