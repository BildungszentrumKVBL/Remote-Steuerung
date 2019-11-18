<?php

namespace AppBundle\Tests\Entity;

use DateTime;
use AppBundle\Entity\Timegrid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TimegridTest.
 */
class TimegridTest extends WebTestCase
{
    /**
     * Test getters and setters for Timegrid class.
     */
    public function testGettersAndSetters()
    {
        $timeGrid  = new Timegrid(new DateTime, new DateTime);
        $timeGrid1 = new Timegrid(Timegrid::intToDateTime(1300), Timegrid::intToDateTime(1345));

        $this->assertNull($timeGrid->getId());
        $this->assertTrue($timeGrid->getEnd() instanceof DateTime);
        $this->assertTrue($timeGrid->getStart() instanceof DateTime);
        $this->assertTrue($timeGrid->getUpdatedAt() instanceof DateTime);

        $this->assertNull($timeGrid1->getId());
        $this->assertTrue($timeGrid1->getEnd() instanceof DateTime);
        $this->assertTrue($timeGrid1->getStart() instanceof DateTime);
        $this->assertTrue($timeGrid1->getUpdatedAt() instanceof DateTime);
    }
}
