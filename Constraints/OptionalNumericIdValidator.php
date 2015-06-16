<?php
namespace Werkspot\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class OptionalNumericIdValidator extends NumericIdValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_null($value)) {
            parent::validate($value, $constraint);
        }
    }
}
