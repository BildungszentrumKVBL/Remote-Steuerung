<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Button;
use AppBundle\Entity\View;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LoadViewsData.
 *
 * This class loads the view data stored in `views.yml`.
 *
 * The view contains the buttons that are present on the view.
 */
class LoadViewsData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $fixtures = Yaml::parse(
            file_get_contents(
                $this->container->get('kernel')->locateResource('@AppBundle/Resources/data/fixtures/views.yml')
            )
        );
        /** @var Button[] $buttons */
        $buttons = $manager->getRepository('AppBundle:Button')->findAll();

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
     * @return integer
     */
    public function getOrder()
    {
        return 3;
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
