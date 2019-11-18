<?php

namespace AppBundle\Entity;

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
    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     *
     * @var int $id
     */
    protected $id;

    /**
     * The start of the lesson.
     *
     * @ORM\Column(name="start", type="time")
     *
     * @var \DateTime $start
     */
    private $start;

    /**
     * The end of the lesson.
     *
     * @ORM\Column(name="end", type="time")
     *
     * @var \DateTime $end
     */
    private $end;

    /**
     * The time when this entity was last updated.
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @var \DateTime $updatedAt
     */
    private $updatedAt;

    /**
     * Timegrid constructor.
     *
     * @param \DateTime $start
     * @param \DateTime $end
     */
    public function __construct(DateTime $start, DateTime $end)
    {
        $this->start     = $start;
        $this->end       = $end;
        $this->updatedAt = new DateTime();
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
    public function getStart(): DateTime
    {
        return $this->start;
    }

    /**
     * This is a helper-method, because the WebUntis-API is incapable of supplying a standard format.
     *
     * @param int $int
     *
     * @return \DateTime
     */
    public static function intToDateTime(int $int): DateTime
    {
        $string = substr_replace($int, ':', -2, 0);

        return DateTime::createFromFormat('H:i', $string);
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): DateTime
    {
        return $this->end;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
