<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class View.
 *
 * This entity represents the view of the web-application.
 * @ORM\Entity()
 */
class View implements \Serializable, \JsonSerializable
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
     * The is the name that helps distinguish the view from others.
     *
     * @ORM\Column()
     *
     * @var string $name
     */
    private $name;

    /**
     * The buttons that are present in this view.
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Button", mappedBy="view")
     *
     * @var Collection $buttons
     */
    private $buttons;

    /**
     * View constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name    = $name;
        $this->buttons = new ArrayCollection();
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
     * @param Button $button
     */
    public function addButton(Button $button)
    {
        if (!$this->buttons->contains($button)) {
            $button->setView($this);
            $this->buttons->add($button);
        }
    }

    /**
     * @param Button $button
     */
    public function removeButton(Button $button)
    {
        $this->buttons->removeElement($button);
    }

    /**
     * @return Collection
     */
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
    public function serialize()
    {
        return serialize(
            [
                'id'   => $this->id,
                'name' => $this->name,
            ]
        );
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data       = unserialize($serialized);
        $this->id   = $data['id'];
        $this->name = $data['name'];
    }

    public function jsonSerialize()
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
