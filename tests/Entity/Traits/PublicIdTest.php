<?php

namespace App\Tests\Entity\Traits;

use App\Entity\User\User;
use App\Tests\AppTestCase;

/**
 * Class PublicIdTest.
 */
class PublicIdTest extends AppTestCase
{
    public function setup(?bool $withDatabase = true, ?bool $withFixtures = true)
    {
        parent::setup(true, false);
    }

    public function testId()
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setGivenName('Max');
        $user->setFamilyName('Mustermann');
        $user->setPassword('123456');
        $user->setEmailVerified(true);

        $doctrine = $this->getContainer()->get('doctrine.orm.entity_manager');
        $doctrine->persist($user);
        $doctrine->flush($user);

        $this->assertNotNull($user->getPublicId());
    }
}
