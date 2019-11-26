<?php

namespace App\DataFixtures;

use App\Entity\AbstractCommand;
use App\Entity\Button;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoadButtonsData.
 *
 * This class loads the fixtures defined in `buttons.yml`.
 */
class LoadButtonsData extends Fixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * Load data fixtures with the passed ObjectManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $fixtures = Yaml::parse(file_get_contents(__DIR__.'/buttons.yml'));
        $buttons = $fixtures['buttons'];

        foreach ($buttons as $button) {
            /** @var AbstractCommand $command */
            $command = $manager->getRepository(AbstractCommand::class)->findOneBy(['name' => $button['command']]);
            $button  = new Button($command, $button['size']);
            $manager->persist($button);
        }
        $manager->flush();
    }

    /**
     * Get the order when this fixtures should be loaded in relation to the other fixtures.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
