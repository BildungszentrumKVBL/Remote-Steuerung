<?php

namespace App\Tests\Entity\User;

use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

/**
 * Class UserTest.
 */
class UserTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = new User();

        // Check default values
        $this->assertNull($user->getId());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getGivenName());
        $this->assertNull($user->getFamilyName());
        $this->assertNull($user->getPassword());
        $this->assertTrue($user->isActive());

        // Dummy functions
        $this->assertNull($user->getSalt());
        $this->assertEquals($user->getRoles(), ['ROLE_USER']);

        // Setting the values
        $this->setTestValues($user);

        // Check the values again
        $this->checkTestValues($user);
    }

    public function testSerialization()
    {
        $user = new User();

        $this->setTestValues($user);

        // Serialisation
        $serialized = serialize($user);
        $this->assertTrue(is_string($serialized));
        $this->assertFalse(empty($serialized));

        // Unserialisation
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(User::class, $unserialized);

        // Check the values again
        $this->checkTestValues($user);
    }

    /**
     * @param User $user
     */
    private function setTestValues(User &$user)
    {
        $user->setEmail('test@test.com');
        $user->setGivenName('Max');
        $user->setFamilyName('Mustermann');
        $user->setPassword('123456');
        $user->setEmailVerified(true);
    }

    /**
     * @param User $user
     */
    private function checkTestValues(User $user)
    {
        $this->assertEquals($user->getEmail(), 'test@test.com');
        $this->assertEquals($user->getGivenName(), 'Max');
        $this->assertEquals($user->getFamilyName(), 'Mustermann');
        $this->assertEquals($user->getPassword(), '123456');
        $this->assertTrue($user->hasEmailVerified());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertFalse($user->isEqualTo(new User()));
    }
}
