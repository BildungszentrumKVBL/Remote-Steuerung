<?php

namespace AppBundle\Tests\EventDispatcher\Event;

use AppBundle\Entity\AbstractCommand;
use AppBundle\Entity\User;
use AppBundle\EventDispatcher\Event\CommandEvent;
use AppBundle\Tests\AppTestCase;

/**
 * Class CommandEventTest.
 */
class CommandEventTest extends AppTestCase
{
    /**
     * Test getters and setters of CommandEvent class.
     */
    public function testGettersAndSetters()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var AbstractCommand $command */
        $command = $em->getRepository(AbstractCommand::class)->findOneBy(['name' => 'cmd_freezeProjector']);
        $user  = User::createFromProperties('BTK', 'bill@thekid.com', 'Billy', 'The Kid');
        $event = new CommandEvent();
        $event->setCommand($command);
        $event->setUser($user);

        $this->assertEquals($command, $event->getCommand());
        $this->assertEquals($user, $event->getUser());
    }
}
