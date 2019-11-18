<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\EventGhostCommand;
use AppBundle\Tests\AppTestCase;

/**
 * Class EventGhostCommandTest.
 */
class EventGhostCommandTest extends AppTestCase
{
    public function testGetUri()
    {
        $em            = $this->getContainer()->get('doctrine.orm.entity_manager');
        $slideXCommand = $em->getRepository(EventGhostCommand::class)->findOneBy(['domain' => 'PowerPoint', 'action' => 'slide_x']);
        $slideXCommand->setAdditionalData(['slide' => 5]);

        $this->assertEquals($slideXCommand->getUri(), '/?PowerPoint&slide_x&slide=5');

        $prevCommand = $em->getRepository(EventGhostCommand::class)->findOneBy(['domain' => 'PowerPoint', 'action' => 'previous']);
        $this->assertEquals($prevCommand->getUri(), '/?PowerPoint&previous');
    }

    public function testGetDataRequirements()
    {
        $em            = $this->getContainer()->get('doctrine.orm.entity_manager');
        $slideXCommand = $em->getRepository(EventGhostCommand::class)->findOneBy(['domain' => 'PowerPoint', 'action' => 'slide_x']);

        $this->assertEquals($slideXCommand->getDataRequirements(), [['variable' => 'slide', 'label' => 'Folie', 'type' => 'tel', 'regex' => '\d+']]);

        $prevCommand = $em->getRepository(EventGhostCommand::class)->findOneBy(['domain' => 'PowerPoint', 'action' => 'previous']);
        $this->assertEmpty($prevCommand->getDataRequirements());
    }

    public function testSetDataRequirements()
    {
        $command = new EventGhostCommand('test', 'test', 'test', 'test', 'test');
        $command->setDataRequirements([['variable' => 'fun']]);
        $this->assertEquals($command->getDataRequirements(), [['variable' => 'fun']]);
    }

    public function testGetAdditionalData()
    {
        $command = new EventGhostCommand('test', 'test', 'test', 'test', 'test');
        $this->assertNull($command->getAdditionalData());
    }

    public function testSetAdditionalData()
    {
        $command = new EventGhostCommand('test', 'test', 'test', 'test', 'test');
        $command->setAdditionalData(['fun' => 'extreme']);

        $this->assertEquals($command->getAdditionalData(), ['fun' => 'extreme']);
    }

}
