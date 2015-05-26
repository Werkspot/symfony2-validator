<?php
namespace Werkspot\Component\Validator\Constraints;

use RuntimeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NumericIdValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $this->ensureConstraintIsInstanceOfNumericValidator($constraint);

        try {
            $value = $this->getValueOrThrowExceptionOnUnsupportedType($value);
            $shouldCheckType = $this->shouldCheckType($constraint);

            if (!$this->isValueNumericAndBiggerThanOne($value, $shouldCheckType)) {
                throw new RuntimeException('Value is not numeric and bigger than one');
            }
        } catch (RuntimeException $e) {
            $this->createViolation($value, $constraint);
        }
    }

    /**
     * @param Constraint $constraint
     */
    private function ensureConstraintIsInstanceOfNumericValidator(Constraint $constraint)
    {
        if (!$constraint instanceof NumericId) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\NumericId');
        }
    }

    /**
     * @param NumericId $constraint
     * @return bool
     */
    private function shouldCheckType(NumericId $constraint)
    {
        return $constraint->checkType;
    }

    /**
     * @param mixed $value
     * @return string
     * @throws RuntimeException
     */
    private function getValueOrThrowExceptionOnUnsupportedType($value)
    {
        if (is_string($value) || is_float($value) || is_integer($value)) {
            return $value;
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }

        throw new RuntimeException('Given value is not a string, float, integer or object with a toString method');
    }

    /**
     * @param mixed $value
     * @param bool $checkType
     * @return bool
     */
    private function isValueNumericAndBiggerThanOne($value, $checkType = false)
    {
        return $this->isValueNumeric($value, $checkType) && $value > 0;
    }

    /**
     * @param mixed $value
     * @param bool $checkType
     * @return bool
     */
    private function isValueNumeric($value, $checkType = false)
    {
        $valueCastedToInt = (int)$value;
        return (string)$valueCastedToInt === (string)$value && (!$checkType || $valueCastedToInt === $value);
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    private function createViolation($value, Constraint $constraint)
    {
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->addViolation();
    }
}