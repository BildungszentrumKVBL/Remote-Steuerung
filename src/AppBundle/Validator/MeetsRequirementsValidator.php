<?php

namespace AppBundle\Validator;

use AppBundle\Entity\EventGhostCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class MeetsRequirementsValidator.
 * Validator for the MeetsRequirements constraint.
 */
class MeetsRequirementsValidator extends ConstraintValidator
{
    /**
     * Validates the constraint.
     *
     * @param mixed      $object
     * @param Constraint $constraint
     */
    public function validate($object, Constraint $constraint)
    {
        /* @var EventGhostCommand $object */
        if ($requirements = $object->getDataRequirements()) {
            $data = $object->getAdditionalData();
            foreach ($requirements as $requirement) {
                if (isset($data[$requirement['variable']])) {
                    $value = $data[$requirement['variable']];
                    if (!$value || !preg_match('/'.$requirement['regex'].'/', $value)) {
                        $this->context->addViolation($constraint->message, []);
                    }
                } else {
                    $this->context->addViolation($constraint->message, []);
                }
            }
        }
    }
}
