<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\AnonUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TestAnonUser.
 */
class AnonUserTest extends WebTestCase
{
    /**
     * Tests getters and setters of AnonUser class.
     */
    public function testGettersAndSetters()
    {
        $user = new AnonUser('test', 'token');

        $this->assertEquals($user->getUsername(), 'test');
        $this->assertEquals($user->getToken(), 'token');
    }
}
