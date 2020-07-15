<?php

namespace App\Validator\Constraints;

use App\Validator\MeetsRequirementsValidator;
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
     */
    public $message = 'Die Anforderungen wurden nicht eingehalten.';

    /**
     * Returns the validator.
     */
    public function validatedBy(): string
    {
        return MeetsRequirementsValidator::class;
    }

    /**
     * Defines itself as a Annotation at the header of a class.
     *
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
