<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
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
    const SETTINGS_FILE = '/config/application.yml';

    /**
     * @var array $settings
     */
    private $settings;

    /**
     * @var string $kernelRoot
     */
    private $kernelRoot;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * SettingsHandler constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->container  = $kernel->getContainer();
        $this->kernelRoot = $this->container->getParameter('kernel.root_dir');
        $this->settings   = Yaml::parse(file_get_contents($this->kernelRoot.self::SETTINGS_FILE))['parameters'];
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings)
    {
        foreach ($settings as $name => $value) {
            if (array_key_exists($name, $this->settings)) {
                if (is_numeric($value) && !(substr($value, 0, 1) === '+' || substr($value, 0, 1) === '0')) {
                    $value = (int) $value;
                } elseif ($value === "true") {
                    $value = true;
                } elseif ($value === "false") {
                    $value = false;
                }
                $this->settings[$name] = $value;
            }
        }
        file_put_contents($this->kernelRoot.self::SETTINGS_FILE, Yaml::dump(['parameters' => $this->settings]));
        $this->clearCache();
    }

    private function clearCache()
    {
        if (!$this->container->getParameter('kernel.debug')) {
            $cacheFile = $this->container->getParameter('kernel.cache_dir').'/appProdProjectContainer.php';
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }
}
