<?php

namespace App\Entity;

use App\Entity\Traits\Id;
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
 * @ORM\DiscriminatorMap({"zulu" = "ZuluCommand", "eventghost" = "EventGhostCommand", "atlona" = "AtlonaCommand"})
 * @UniqueEntity("name")
 */
abstract class AbstractCommand
{
    use Id;

    /**
     * This is the unique identifier to distinguish between different commands..
     *
     * @ORM\Column(name="name", type="string")
     *
     * @var string
     */
    private $name;

    /**
     * Name of the material design icon that suits this command the best.
     *
     * @ORM\Column(name="icon", type="string")
     *
     * @var string
     */
    private $icon;

    /**
     * Label which helps the user to guess what this command does.
     *
     * @ORM\Column(name="label", type="string")
     *
     * @var string
     */
    private $label;

    /**
     * AbstractCommand constructor.
     */
    public function __construct(string $name, string $icon, string $label)
    {
        $this->name  = $name;
        $this->icon  = $icon;
        $this->label = $label;
    }

    /**
     * Returns the URI for the given command.
     */
    abstract public function getUri(): string;

    public function getName(): string
    {
        return $this->name;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
