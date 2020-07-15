<?php

namespace App\Entity;

use App\Validator\Constraints\MeetsRequirements;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class EventGhostCommand.
 *
 * This class extends the `AbstractCommand` and implements a universal method of triggering actions on the client-side.
 *
 * @MeetsRequirements()
 *
 * @ORM\Entity()
 */
class EventGhostCommand extends AbstractCommand
{
    /**
     * Defines what programm this command controls.
     *
     * @ORM\Column(name="domain", type="string")
     *
     * @var string
     */
    private $domain;

    /**
     * Action name which describes what it does.
     *
     * @ORM\Column(name="action", type="string")
     *
     * @var string
     */
    private $action;

    /**
     * Additional Data for EventGhost commands.
     * It holds the name of the variables and a RegEx for validating the value.
     *
     * @ORM\Column(name="data_requirements", type="json_array", nullable=true)
     *
     * @var array
     */
    private $dataRequirements;

    /**
     * Additional data which will be needed for special EventGhost requests that requires parameters.
     *
     * @var array
     */
    private $additionalData;

    /**
     * EventGhostCommand constructor.
     *
     * @param string|null $domain
     */
    public function __construct(string $name, string $icon, string $label, string $domain, string $action)
    {
        parent::__construct($name, $icon, $label);
        $this->domain = $domain;
        $this->action = $action;
    }

    public function getUri(): string
    {
        return sprintf('/?%s&%s%s', $this->domain, $this->action, $this->formatAdditionalData());
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return mixed
     */
    public function getDataRequirements(): ?array
    {
        return $this->dataRequirements;
    }

    public function setDataRequirements(array $dataRequirements): void
    {
        $this->dataRequirements = $dataRequirements;
    }

    /**
     * Sets the data that is needed for this command.
     */
    public function setAdditionalData(array $data): void
    {
        $this->additionalData = $data;
    }

    /**
     * @return mixed
     */
    public function getAdditionalData(): ?array
    {
        return $this->additionalData;
    }

    /**
     * Formats and returns the additional data as an URL-ready string.
     */
    private function formatAdditionalData(): string
    {
        $string = '';
        if (!empty($this->additionalData)) {
            foreach ($this->additionalData as $key => $data) {
                $string .= sprintf('&%s=%s', $key, $data);
            }
        }

        return $string;
    }
}
