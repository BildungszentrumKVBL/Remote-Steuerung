<?php

namespace App\Entity;

use App\Entity\Traits\Id;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Timegrid.
 *
 * This entity represents the schedule of the lessons. It should be automatically updated periodically in order to work.
 *
 * @ORM\Entity()
 * @ORM\Table(name="timegrid")
 */
class Timegrid
{
    use Id;

    /**
     * The start of the lesson.
     *
     * @ORM\Column(name="start", type="time")
     *
     * @var \DateTime
     */
    private $start;

    /**
     * The end of the lesson.
     *
     * @ORM\Column(name="end", type="time")
     *
     * @var \DateTime
     */
    private $end;

    /**
     * The time when this entity was last updated.
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * Timegrid constructor.
     */
    public function __construct(DateTime $start, DateTime $end)
    {
        $this->start     = $start;
        $this->end       = $end;
        $this->updatedAt = new DateTime();
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    /**
     * This is a helper-method, because the WebUntis-API is incapable of supplying a standard format.
     */
    public static function intToDateTime(int $int): DateTime
    {
        $string = substr_replace($int, ':', -2, 0);

        return DateTime::createFromFormat('H:i', $string);
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
