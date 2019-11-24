<?php

namespace App\Tests\Entity\Traits;

use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

/**
 * Class IdTest.
 */
class IdTest extends TestCase
{
    public function testId()
    {
        $user = new User();
        $user->setId(5);
        $this->assertEquals(5, $user->getId());
    }
}
