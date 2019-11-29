<?php

namespace App\DataFixtures;

use App\Entity\AbstractCommand;
use App\Entity\Button;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoadButtonsData.
 *
 * This class loads the fixtures defined in `buttons.yml`.
 */
class LoadButtonsData extends Fixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed ObjectManager.
     */
    public function load(ObjectManager $manager): void
    {
        $fixtures = Yaml::parse(file_get_contents(__DIR__.'/buttons.yml'));
        $buttons  = $fixtures['buttons'];

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
     */
    public function getOrder(): int
    {
        return 2;
    }
}
