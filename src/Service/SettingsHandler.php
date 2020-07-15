<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SettingsHandler.
 *
 * This service handles the settings for the application.
 */
class SettingsHandler
{
    /**
     * Settings file for the general application.
     */
    private const SETTINGS_FILE = '/config/packages/application.yml';

    /**
     * @var array
     */
    private $settings;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * SettingsHandler constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->settings   = Yaml::parse(file_get_contents($this->projectDir.self::SETTINGS_FILE))['parameters'];
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings)
    {
        foreach ($settings as $name => $value) {
            if (array_key_exists($name, $this->settings)) {
                if (is_numeric($value) && !('+' === substr($value, 0, 1) || '0' === substr($value, 0, 1))) {
                    $value = (int) $value;
                } elseif ('true' === $value) {
                    $value = true;
                } elseif ('false' === $value) {
                    $value = false;
                }
                $this->settings[$name] = $value;
            }
        }
        file_put_contents($this->projectDir.self::SETTINGS_FILE, Yaml::dump(['parameters' => $this->settings]));
    }
}
