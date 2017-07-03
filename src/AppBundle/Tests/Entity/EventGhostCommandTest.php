<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\EventGhostCommand;
use AppBundle\Tests\AppTestCase;

/**
 * Class EventGhostCommandTest.
 *
 * @IPA
 */
class EventGhostCommandTest extends AppTestCase
{
    /**
     * @IPA
     */
    public function testGetUri()
    {
        $em            = $this->getContainer()->get('doctrine.orm.entity_manager');
        $slideXCommand = $em->getRepository('AppBundle:EventGhostCommand')->findOneBy(['domain' => 'PowerPoint', 'action' => 'slide_x']);
        $slideXCommand->setAdditionalData(['slide' => 5]);

        $this->assertEquals($slideXCommand->getUri(), '/?PowerPoint&slide_x&slide=5');

        $prevCommand = $em->getRepository('AppBundle:EventGhostCommand')->findOneBy(['domain' => 'PowerPoint', 'action' => 'previous']);
        $this->assertEquals($prevCommand->getUri(), '/?PowerPoint&previous');
    }

    /**
     * @IPA
     */
    public function testGetDataRequirements()
    {
        $em            = $this->getContainer()->get('doctrine.orm.entity_manager');
        $slideXCommand = $em->getRepository('AppBundle:EventGhostCommand')->findOneBy(['domain' => 'PowerPoint', 'action' => 'slide_x']);

        $this->assertEquals($slideXCommand->getDataRequirements(), [['variable' => 'slide', 'label' => 'Folie', 'type' => 'tel', 'regex' => '\d+']]);

        $prevCommand = $em->getRepository('AppBundle:EventGhostCommand')->findOneBy(['domain' => 'PowerPoint', 'action' => 'previous']);
        $this->assertEmpty($prevCommand->getDataRequirements());
    }

    /**
     * @IPA
     */
    public function testSetDataRequirements()
    {
        $command = new EventGhostCommand('test', 'test', 'test', 'test', 'test');
        $command->setDataRequirements([['variable' => 'fun']]);
        $this->assertEquals($command->getDataRequirements(), [['variable' => 'fun']]);
    }

    /**
     * @IPA
     */
    public function testGetAdditionalData()
    {
        $command = new EventGhostCommand('test', 'test', 'test', 'test', 'test');
        $this->assertNull($command->getAdditionalData());
    }

    /**
     * @IPA
     */
    public function testSetAdditionalData()
    {
        $command = new EventGhostCommand('test', 'test', 'test', 'test', 'test');
        $command->setAdditionalData(['fun' => 'extreme']);

        $this->assertEquals($command->getAdditionalData(), ['fun' => 'extreme']);
    }


}
