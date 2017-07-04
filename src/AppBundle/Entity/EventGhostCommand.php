<?php

namespace AppBundle\Entity;

use AppBundle\Validator\Constraints\MeetsRequirements;
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
     * @var string $domain
     */
    private $domain;

    /**
     * Action name which describes what it does.
     *
     * @ORM\Column(name="action", type="string")
     *
     * @var string $action
     */
    private $action;

    /**
     * Additional Data for EventGhost commands.
     * It holds the name of the variables and a RegEx for validating the value.
     *
     * \IPA
     *
     * @ORM\Column(name="data_requirements", type="json_array", nullable=true)
     *
     * @var array $dataRequirements
     */
    private $dataRequirements;

    /**
     * Additional data which will be needed for special EventGhost requests that requires parameters.
     *
     * \IPA
     *
     * @var array $additionalData
     */
    private $additionalData;

    /**
     * EventGhostCommand constructor.
     *
     * @param string      $name
     * @param string      $icon
     * @param string      $label
     * @param null|string $domain
     * @param string      $action
     */
    public function __construct(string $name, string $icon, string $label, string $domain, string $action)
    {
        parent::__construct($name, $icon, $label);
        $this->domain = $domain;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return sprintf('/?%s&%s%s', $this->domain, $this->action, $this->formatAdditionalData());
    }

    /**
     * \IPA
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * \IPA
     *
     * @return mixed
     */
    public function getDataRequirements()
    {
        return $this->dataRequirements;
    }

    /**
     * \IPA
     *
     * @param array $dataRequirements
     */
    public function setDataRequirements(array $dataRequirements)
    {
        $this->dataRequirements = $dataRequirements;
    }

    /**
     * Sets the data that is needed for this command.
     *
     * \IPA
     *
     * @param array $data
     */
    public function setAdditionalData(array $data)
    {
        $this->additionalData = $data;
    }

    /**
     * \IPA
     *
     * @return mixed
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * Formats and returns the additional data as an URL-ready string.
     *
     * \IPA
     *
     * @return string
     */
    private function formatAdditionalData(): string
    {
        $string = '';
        if ($this->additionalData) {
            foreach ($this->additionalData as $key => $data) {
                $string .= sprintf('&%s=%s', $key, $data);
            }
        }

        return $string;
    }
}
