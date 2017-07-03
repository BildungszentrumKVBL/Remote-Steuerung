<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Log;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class LogTest.
 */
class LogTest extends WebTestCase
{
    /**
     * Tests getters and setters of Log class.
     */
    public function testGettersAndSetters()
    {
        $user = User::createFromProperties('test', 'test@test.com', 'Test', 'Test');
        $log  = new Log('Test', Log::LEVEL_INFO, $user);

        $this->assertTrue($log->getDateTime() instanceof \DateTime);
        $this->assertNull($log->getId());
        $this->assertEquals($log->getLevel(), Log::LEVEL_INFO);
        $this->assertEquals($log->getMessage(), 'Test');
        $this->assertEquals($log->getUser(), $user);
    }

    /**
     * Tests jsonSerializable function.
     */
    public function testJsonSerializable()
    {
        $user       = User::createFromProperties('test', 'test@test.com', 'Test', 'Test');
        $log        = new Log('Test', Log::LEVEL_INFO, $user);
        $serialized = json_encode($log);
        json_decode($serialized);

        $this->assertTrue(is_string($serialized));
        $this->assertTrue(json_last_error() === JSON_ERROR_NONE);
    }
}
