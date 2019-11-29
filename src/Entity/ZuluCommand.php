<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ZuluCommand.
 *
 * This entity represents the commands that do work on a Zulu-microcontroller.
 *
 * @ORM\Entity()
 */
class ZuluCommand extends AbstractCommand
{
    /**
     * The id of the command that is used in the default web-interface of the Zulu.
     *
     * @ORM\Column(name="command_id", type="string", type="string")
     *
     * @var string
     */
    private $commandId;

    /**
     * ZuluCommand constructor.
     *
     * @param string $commandId
     */
    public function __construct(string $name, string $icon, string $label, $commandId)
    {
        parent::__construct($name, $icon, $label);
        $this->commandId = (string) $commandId;
    }

    public function getCommandId(): string
    {
        return $this->commandId;
    }

    public function getUri(): string
    {
        return '/status.xml?KeyDown='.$this->commandId;
    }
}
