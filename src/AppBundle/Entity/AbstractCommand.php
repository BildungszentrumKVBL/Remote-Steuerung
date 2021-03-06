<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class AbstractCommand.
 *
 * This class is the base of all commands available in this application. Both `ZuluCommand` and the `EventGhostCommand`
 * inherit from this class, in order to remove redundancy.
 * This class can be further abstracted when needed.
 *
 * @ORM\Entity()
 * @ORM\Table(name="command")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"zulu" = "ZuluCommand", "eventghost" = "EventGhostCommand"})
 * @UniqueEntity("name")
 */
abstract class AbstractCommand
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
     * This is the unique identifier to distinguish between different commands..
     *
     * @ORM\Column(name="name", type="string")
     *
     * @var string $name
     */
    private $name;

    /**
     * Name of the material design icon that suits this command the best.
     *
     * @ORM\Column(name="icon", type="string")
     *
     * @var string $icon
     */
    private $icon;

    /**
     * Label which helps the user to guess what this command does.
     *
     * @ORM\Column(name="label", type="string")
     *
     * @var string $label
     */
    private $label;

    /**
     * AbstractCommand constructor.
     *
     * @param string $name
     * @param string $icon
     * @param string $label
     */
    public function __construct(string $name, string $icon, string $label)
    {
        $this->name  = $name;
        $this->icon  = $icon;
        $this->label = $label;
    }

    /**
     * Returns the URI for the given command.
     *
     * @return string
     */
    abstract public function getUri(): string;

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
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
