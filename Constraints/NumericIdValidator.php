<?php
namespace Werkspot\Component\Validator\Constraints;

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
        $this->ensureValueCanBeAccessedAsAString($value);

        $shouldCheckType = $this->shouldCheckType($constraint);
        $scalarValue = $this->getScalarValue($value);

        if (!$this->isValueNumericAndBiggerThanOne($scalarValue, $shouldCheckType)) {
            $this->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($scalarValue))
                ->addViolation();
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
     * @param $value
     */
    private function ensureValueCanBeAccessedAsAString($value)
    {
        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }
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
     * @param NumericId $constraint
     * @return bool
     */
    private function shouldCheckType(NumericId $constraint)
    {
        return $constraint->checkType;
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
     * @param $value
     * @return string
     */
    private function getScalarValue($value)
    {
        if (is_object($value)) {
            return (string)$value;
        }

        return $value;
    }
}