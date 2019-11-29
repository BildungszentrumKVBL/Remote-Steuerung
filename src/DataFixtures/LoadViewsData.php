<?php

namespace App\DataFixtures;

use App\Entity\Button;
use App\Entity\View;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoadViewsData.
 *
 * This class loads the view data stored in `views.yml`.
 *
 * The view contains the buttons that are present on the view.
 */
class LoadViewsData extends Fixture implements OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed ObjectManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $fixtures = Yaml::parse(file_get_contents(__DIR__.'/views.yml'));
        /** @var Button[] $buttons */
        $buttons = $manager->getRepository(Button::class)->findAll();

        foreach ($fixtures as $name => $commands) {
            $view = new View($name);
            foreach ($commands as $command) {
                foreach ($buttons as $button) {
                    if ($button->getCommand()->getName() === $command) {
                        $clone = clone $button;
                        $view->addButton($clone);
                        $manager->persist($clone);
                    }
                }
                $manager->persist($view);
            }
            $manager->flush();
        }
    }

    /**
     * Get the order when this fixtures should be loaded in relation to the other fixtures.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return 3;
    }
}
