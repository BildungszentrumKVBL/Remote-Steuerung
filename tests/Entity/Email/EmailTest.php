<?php

namespace App\Tests\Entity\Email;

use App\Entity\Email\Email;
use PHPUnit\Framework\TestCase;

/**
 * Class EmailTest.
 */
class EmailTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $email = new Email();

        $this->assertNull($email->getContent());
        $this->assertNull($email->getEmailAddress());
        $this->assertEquals(0, $email->getViewCount());

        $email->setContent('content');
        $email->setEmailAddress('email@email.com');
        $email->incrementViewCount();

        $this->assertEquals('content', $email->getContent());
        $this->assertEquals('email@email.com', $email->getEmailAddress());
        $this->assertEquals(1, $email->getViewCount());

        $email->setViewCount(5);
        $this->assertEquals(5, $email->getViewCount());
    }
}
