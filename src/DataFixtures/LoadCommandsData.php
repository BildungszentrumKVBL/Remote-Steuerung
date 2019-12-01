<?php

namespace App\DataFixtures;

use App\Entity\AtlonaCommand;
use App\Entity\EventGhostCommand;
use App\Entity\ZuluCommand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoadCommandsData.
 *
 * This class loads the fixtures defined in `commands.yml`.
 *
 * It fills the database with the `ZuluCommands` and the `EventGhostCommands`.
 */
class LoadCommandsData extends Fixture implements OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed ObjectManager.
     */
    public function load(ObjectManager $manager): void
    {
        $fixtures = Yaml::parse(file_get_contents(__DIR__.'/commands.yml'));
        $zuluFixtures = $fixtures['Zulu'];
        $eventGhostFixtures = $fixtures['EventGhost'];
        $atlonaFixtures = $fixtures['Atlona'];

        foreach ($zuluFixtures as $name => $fixture) {
            $zuluCommand = new ZuluCommand(
                $name,
                $fixture['assets']['icon'],
                $fixture['assets']['label'],
                $fixture['data']['id']
            );
            $manager->persist($zuluCommand);
        }

        foreach ($eventGhostFixtures as $domain => $fixtures) {
            foreach ($fixtures as $name => $fixture) {
                $command = new EventGhostCommand(
                    $name,
                    $fixture['assets']['icon'],
                    $fixture['assets']['label'],
                    $domain,
                    $fixture['data']['action']
                );
                if (!empty($fixture['data']['requirements'])) {
                    $command->setDataRequirements($fixture['data']['requirements']);
                }
                $manager->persist($command);
            }
        }

        foreach ($atlonaFixtures as $name => $fixtures) {
            $command = new AtlonaCommand(
                $name,
                $fixtures['assets']['icon'],
                $fixtures['assets']['label'],
                $fixtures['data']['name'],
                $fixtures['data']['payload'],
                $fixtures['data']['telnet'] ?? true
            );
            $manager->persist($command);
        }

        $manager->flush();
    }

    /**
     * Get the order when this fixtures should be loaded in relation to the other fixtures.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return 1;
    }
}
