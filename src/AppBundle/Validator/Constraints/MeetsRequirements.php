<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class MeetsRequirements.
 *
 * This class is used for checking the validity of the requirements of each command.
 *
 * @Annotation
 */
class MeetsRequirements extends Constraint
{
    /**
     * Answer when the validation is violated.
     *
     * @var string $message
     */
        public $message = 'Die Anforderungen wurden nicht eingehalten.';

    /**
     * Returns the validator.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'AppBundle\Validator\MeetsRequirementsValidator';
    }

    /**
     * Defines itself as a Annotation at the header of a class.
     *
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
