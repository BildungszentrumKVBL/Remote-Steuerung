<?php

namespace AppBundle\Tests\Validator\Constraints;

use AppBundle\Tests\AppTestCase;
use AppBundle\Validator\Constraints\MeetsRequirements;

/**
 * Class MeetsRequirementsTest.
 *
 * @IPA
 */
class MeetsRequirementsTest extends AppTestCase
{
    /**
     * @IPA
     */
    public function testMessage()
    {
        $constraint = new MeetsRequirements();
        $this->assertEquals($constraint->message, 'Die Anforderungen wurden nicht eingehalten.');
    }

    /**
     * @IPA
     */
    public function testValidatedBy()
    {
        $constraint = new MeetsRequirements();
        $this->assertEquals($constraint->validatedBy(), 'AppBundle\Validator\MeetsRequirementsValidator');
    }

    /**
     * @IPA
     */
    public function testGetTargets()
    {
        $constraint = new MeetsRequirements();
        $this->assertEquals($constraint->getTargets(), MeetsRequirements::CLASS_CONSTRAINT);
    }
}
