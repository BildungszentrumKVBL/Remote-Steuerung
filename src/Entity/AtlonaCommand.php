<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AtlonaCommand.
 *
 * @ORM\Entity
 */
class AtlonaCommand extends AbstractCommand
{
    /**
     * The command name, described in the API PDF for AT-UHD-SW-510W.
     *
     * @ORM\Column(name="command_name", type="string")
     *
     * @var string
     */
    private $commandName;

    /**
     * TODO: Refactor to JSON-Type after updating Database.
     *
     * @ORM\Column(name="command_payload", type="string")
     *
     * @var string
     */
    private $commandPayload;

    /**
     * @ORM\Column(name="telnet", type="boolean")
     *
     * @var bool
     */
    private $telnet;

    public function __construct(
        string $name,
        string $icon,
        string $label,
        string $commandName,
        array $commandPayload,
        bool $isTelnet = true
    ) {
        parent::__construct($name, $icon, $label);

        $this->commandName = $commandName;
        $this->setCommandPayload($commandPayload);
        $this->telnet      = $isTelnet;
    }

    public function getCommandName(): string
    {
        return $this->commandName;
    }

    public function getCommandPayload(): array
    {
        return json_decode($this->commandPayload, true);
    }

    public function setCommandPayload(array $payload): void
    {
        $this->commandPayload = json_encode($payload);
    }

    public function isTelnet(): bool
    {
        return $this->telnet;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): string
    {
        return '';
    }
}
