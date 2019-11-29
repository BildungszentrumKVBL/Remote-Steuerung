<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Serializable;

/**
 * Class View.
 *
 * This entity represents the view of the web-application.
 *
 * @ORM\Entity()
 */
class View implements Serializable, JsonSerializable
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
     * The is the name that helps distinguish the view from others.
     *
     * @ORM\Column()
     *
     * @var string
     */
    private $name;

    /**
     * The buttons that are present in this view.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Button", mappedBy="view")
     *
     * @var Collection
     */
    private $buttons;

    /**
     * View constructor.
     */
    public function __construct(string $name)
    {
        $this->name    = $name;
        $this->buttons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addButton(Button $button): void
    {
        if (!$this->buttons->contains($button)) {
            $button->setView($this);
            $this->buttons->add($button);
        }
    }

    public function removeButton(Button $button): void
    {
        $this->buttons->removeElement($button);
    }

    public function getButtons(): Collection
    {
        return $this->buttons;
    }

    /**
     * This function is a so called `magic function`. Their purpose is very functional.
     *
     * This `__toString()`-function will be called if the object will be casted into a string.
     *
     * For instance when trying to use `print()` or `echo` on this object.
     */
    public function __toString(): string
    {
        return $this->name;
    }

    public function serialize(): string
    {
        return serialize(
            [
                'id'   => $this->id,
                'name' => $this->name,
            ]
        );
    }

    public function unserialize($serialized): void
    {
        $data       = unserialize($serialized);
        $this->id   = $data['id'];
        $this->name = $data['name'];
    }

    public function jsonSerialize(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
