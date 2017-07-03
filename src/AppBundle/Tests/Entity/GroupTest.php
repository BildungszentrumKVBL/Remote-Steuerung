<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class GroupTest.
 */
class GroupTest extends WebTestCase
{
    /**
     * Tests getters and setters for Group class.
     */
    public function testGettersAndSetters()
    {
        $group = new Group('Teacher', ['ROLE_TEACHER']);

        $this->assertTrue($group->getName() === 'Teacher');
        $this->assertTrue($group->getRoles() === ['ROLE_TEACHER']);
        $this->assertNull($group->getId());
    }
}
