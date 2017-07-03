<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\User;
use AppBundle\Entity\Zulu;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Console\Application;
use \Symfony\Component\Console\Input\ArrayInput;

/**
 * Class UserRepositoryTest.
 */
class UserRepositoryTest extends WebTestCase
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
        $this->application->setAutoExit(false);

        $this->runConsole("doctrine:schema:drop", ["--force" => true]);
        $this->runConsole("doctrine:schema:create");
        $this->runConsole("doctrine:fixtures:load");

        $this->container = self::$kernel->getContainer();
        $em              = $this->container->get('doctrine')->getManager();

        $this->em = $em;
    }

    /**
     * @param       string $command
     * @param array $options
     *
     * @return null|integer
     */
    protected function runConsole($command, Array $options = [])
    {
        $options["-e"] = "test";
        $options["-q"] = null;
        $options       = array_merge($options, ['command' => $command]);

        return $this->application->run(new ArrayInput($options));
    }

    /**
     * Tests getLockedZulu function.
     */
    public function testGetLockedZulu()
    {
        $repo = $this->em->getRepository('AppBundle:User');
        $this->assertNull($repo->getLockedZulu('username'));

        $zulu = new Zulu('127.0.0.1');
        $this->em->persist($zulu);
        $this->em->flush($zulu);

        $user = User::createFromProperties('test', 'test@test.com', 'test', 'test');
        $room = $this->em->getRepository('AppBundle:Room')->findOneBy(['name'=> 'A11']);

        $zulu = $this->em->getRepository('AppBundle:Zulu')->findOneBy(['room' => $room]);
        $zulu->lock($user);
        $this->em->persist($user);
        $this->em->persist($zulu);
        $this->em->flush();

        $this->assertEquals($zulu, $repo->getLockedZulu($user->getUsername()));
    }
}
