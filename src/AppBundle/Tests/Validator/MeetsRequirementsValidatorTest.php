<?php

namespace AppBundle\Tests\Validator;

use AppBundle\Entity\EventGhostCommand;
use AppBundle\Tests\AppTestCase;

/**
 * Class MeetsRequirementsValidatorTest.
 */
class MeetsRequirementsValidatorTest extends AppTestCase
{
    public function testValidate()
    {
        $validator = $this->getContainer()->get('validator');
        /* @var EventGhostCommand $switchSlideCommand */
        $switchSlideCommand = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository(EventGhostCommand::class)->findOneBy(['action' => 'slide_x']);
        $violations = $validator->validate($switchSlideCommand);
        $this->assertEquals(count($violations), 1);

        $switchSlideCommand->setAdditionalData(['please' => 'fail']);
        $violations = $validator->validate($switchSlideCommand);
        $this->assertEquals(count($violations), 1);

        $switchSlideCommand->setAdditionalData(['slide' => 'fail']);
        $violations = $validator->validate($switchSlideCommand);
        $this->assertEquals(count($violations), 1);

        $switchSlideCommand->setAdditionalData(['slide' => 6]);
        $violations = $validator->validate($switchSlideCommand);
        $this->assertEquals(count($violations), 0);
    }
}
