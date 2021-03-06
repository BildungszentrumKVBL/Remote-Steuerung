<?php

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Tests\AppTestCase;
use AppBundle\Validator\Constraints\MeetsRequirements;

/**
 * Class MeetsRequirementsTest.
 */
class MeetsRequirementsTest extends AppTestCase
{
    public function testMessage()
    {
        $constraint = new MeetsRequirements();
        $this->assertEquals($constraint->message, 'Die Anforderungen wurden nicht eingehalten.');
    }

    public function testValidatedBy()
    {
        $constraint = new MeetsRequirements();
        $this->assertEquals($constraint->validatedBy(), 'AppBundle\Validator\MeetsRequirementsValidator');
    }

    public function testGetTargets()
    {
        $constraint = new MeetsRequirements();
        $this->assertEquals($constraint->getTargets(), MeetsRequirements::CLASS_CONSTRAINT);
    }
}
