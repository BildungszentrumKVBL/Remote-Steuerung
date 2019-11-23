<?php

namespace App\DataFixtures;

use App\Entity\EventGhostCommand;
use App\Entity\ZuluCommand;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoadCommandsData.
 *
 * This class loads the fixtures defined in `commands.yml`.
 *
 * It fills the database with the `ZuluCommands` and the `EventGhostCommands`.
 */
class LoadCommandsData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $fixtures = Yaml::parse(file_get_contents(__DIR__.'/commands.yml'));
        $zuluFixtures       = $fixtures['Zulu'];
        $eventGhostFixtures = $fixtures['EventGhost'];

        foreach ($zuluFixtures as $name => $fixture) {
            $zuluCommand = new ZuluCommand($name, $fixture['assets']['icon'], $fixture['assets']['label'], $fixture['data']['id']);
            $manager->persist($zuluCommand);
        }

        foreach ($eventGhostFixtures as $domain => $fixtures) {
            foreach ($fixtures as $name => $fixture) {
                $command = new EventGhostCommand($name, $fixture['assets']['icon'], $fixture['assets']['label'], $domain, $fixture['data']['action']);
                if (!empty($fixture['data']['requirements'])) {
                    $command->setDataRequirements($fixture['data']['requirements']);
                }
                $manager->persist($command);
            }
        }

        $manager->flush();
    }

    /**
     * Get the order when this fixtures should be loaded in relation to the other fixtures.
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
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
