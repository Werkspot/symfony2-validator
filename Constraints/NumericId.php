<?php
namespace Werkspot\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NumericId extends Constraint
{
    /**
     * @var string
     */
    public $message = 'This id is not valid.';

    /**
     * @var bool
     */
    public $checkType = false;
}
