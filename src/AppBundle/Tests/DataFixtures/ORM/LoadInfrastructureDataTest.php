<?php

namespace AppBundle\Tests\DataFixtures\ORM;

use AppBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class LoadInfrastructureDataTest.
 */
class LoadInfrastructureDataTest extends WebTestCase
{
    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var Container $container
     */
    private $container;

    /**
     * @var Application $application
     */
    private $application;

    /**
     * Prepares environment for tests.
     */
    public function setup()
    {
        self::bootKernel();
        $this->application = new \Symfony\Bundle\FrameworkBundle\Console\Application(self::$kernel);
        $this->container   = self::$kernel->getContainer();
        $this->em          = $this->container->get('doctrine.orm.entity_manager');
        $this->application->setAutoExit(false);
    }

    /**
     * @param       $command
     * @param array $options
     *
     * @return mixed
     */
    protected function runConsole($command, Array $options = [])
    {
        $options['-e'] = 'test';
        $options['-q'] = null;
        $options       = array_merge($options, ['command' => $command]);

        return $this->application->run(new ArrayInput($options));
    }

    /**
     * Tests load function of LoadZuluData class.
     */
    public function testLoad()
    {
        $this->runConsole("doctrine:schema:drop", ["--force" => true]);
        $this->runConsole("doctrine:schema:create");
        $this->runConsole("doctrine:fixtures:load");

        $groups = $this->em->getRepository(Group::class)->findAll();

        $this->assertEquals(count($groups), 4);
    }
}
