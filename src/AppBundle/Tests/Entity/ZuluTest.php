<?php

namespace AppBundle\Tests\Entity;

use Application;
use DateTime;
use Room;
use User;
use Zulu;
use EntityManager;
use WebTestCase;
use Application;
use ArrayInput;
use Container;
/**
 * Class ZuluTest.
 */
class ZuluTest extends WebTestCase
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
    public function setup()
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
        $this->application->setAutoExit(false);
        $this->runConsole("doctrine:schema:drop", ["--force" => true]);
        $this->runConsole("doctrine:schema:create");
        $this->runConsole("doctrine:fixtures:load");
        $this->container = self::$kernel->getContainer();
        $em = $this->container->get('doctrine')->getManager();
        $this->em = $em;
    }
    /**
     * @param       $command
     * @param array $options
     *
     * @return mixed
     */
    protected function runConsole($command, array $options = [])
    {
        $options["-e"] = "test";
        $options["-q"] = null;
        $options = array_merge($options, ['command' => $command]);
        return $this->application->run(new ArrayInput($options));
    }
    /**
     * Tests getters and setters of Zulu class.
     */
    public function testGettersAndSetters()
    {
        $room = new Room('Test123');
        $zulu = new Zulu('192.168.1.1');
        $room->setZulu($zulu);
        $this->assertTrue($zulu->isActive());
        $this->assertFalse($zulu->isLocked());
        $this->assertNull($zulu->getLockedBy());
        $this->assertNull($zulu->getLockedSince());
        $this->assertNull($zulu->getId());
        $this->assertEquals($zulu->getIp(), '192.168.1.1');
        $this->assertEquals($zulu->getRoom(), $room);
    }
    /**
     * Tests lock and unlock function.
     */
    public function testLockAndUnlock()
    {
        $user = User::createFromProperties('test', 'test@test.com', 'te', 'st');
        $zulu = new Zulu('192.168.1.1');
        $zulu->lock($user);
        $this->assertTrue($zulu->getLockedSince() instanceof DateTime);
        $this->assertEquals($zulu->getLockedBy(), 'test');
        $this->assertTrue($zulu->isLocked());
        $zulu->unlock();
        $this->assertNull($zulu->getLockedSince());
        $this->assertNull($zulu->getLockedBy());
        $this->assertFalse($zulu->isLocked());
    }
    /**
     * Tests jsonSerialize function.
     */
    public function testJsonSerialize()
    {
        $zulu = $this->em->getRepository(Zulu::class)->findOneBy(['id' => 1]);
        $serialized = json_encode($zulu);
        json_decode($serialized);
        $this->assertTrue(is_string($serialized));
        $this->assertTrue(json_last_error() === JSON_ERROR_NONE);
    }
}
