<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use AppBundle\Tests\AppTestCase;

/**
 * Class UserTest.
 */
class UserTest extends AppTestCase
{
    /**
     * Tests getters and setters of the User class.
     */
    public function testGettersAndSetters()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $group = $em->getRepository('AppBundle:Group')->findOneBy(['name' => 'IT-Teacher']);

        $user = User::createFromProperties('BTK', 'bill@thekid.com', 'Billy', 'The Kid');
        $user->setDn('DN')->setLdapGroups(['anything'])->addGroup($group);

        $this->assertNull($user->getId());
        $this->assertEquals('Billy', $user->getFirstName());
        $this->assertEquals('The Kid', $user->getLastName());
        $this->assertEquals('BTK', $user->getUsername());
        $this->assertEquals('DN', $user->getDn());
        $this->assertNull($user->getSettings()->getView());
        $this->assertTrue($user->isLdapUser());
        $this->assertTrue($user->getLdapGroups() === []);
        $this->assertTrue($user->getGroups()->contains($group));

        $em->persist($user);
        $em->flush();

        $this->assertNotNull($user->getSettings()->getView());
    }
}
