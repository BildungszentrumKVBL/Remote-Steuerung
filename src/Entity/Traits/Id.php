<?php


namespace App\Entity\Traits;


/**
 * Trait Id.
 */
trait Id
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @internal
     *
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
