<?php
namespace Werkspot\Component\Validator\Tests\Constraints;

use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Validation;
use Werkspot\Component\Validator\Constraints\NumericId;
use Werkspot\Component\Validator\Constraints\NumericIdValidator;

class NumericIdValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Werkspot\Component\Validator\Constraints\NumericId
     */
    public function testValidate_withInvalidConstraint_throwsException()
    {
        $this->validator->validate(1, new DummyConstraint());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage string
     */
    public function testValidate_withValueThatCanNotBeCastedToString()
    {
        $this->validator->validate(new \stdClass(), new NumericId());
    }

    /**
     * @dataProvider getInvalidIdData
     *
     * @param mixed $value
     * @param NumericId $constraint
     */
    public function testValidate_withInvalidValue($value, NumericId $constraint)
    {
        $this->validator->validate($value, $constraint);

        $expectedValue = $value;

        if (is_string($expectedValue)) {
            $expectedValue = '"' . $expectedValue . '"';
        } else if(is_object($expectedValue)) {
            $expectedValue = '"' . strval($expectedValue) . '"';
        }

        $this->buildViolation('This id is not valid.')
            ->setParameter('{{ value }}', $expectedValue)
            ->assertRaised();
    }

    /**
     * @return array
     */
    public function getInvalidIdData()
    {
        return [
            ['', new NumericId()],
            [0, new NumericId()],
            ['1', new NumericId(['checkType' => true])],
            [1.9, new NumericId()],
            [new StubValueWithToStringMethod('3'), new NumericId(['checkType' => true])],
            ['4a', new NumericId()],
            ['5  ', new NumericId()],
            [' 6 ', new NumericId()],
            [new StubValueWithToStringMethod(' 7b '), new NumericId(['checkType' => false])],
        ];
    }

    /**
     * @dataProvider getValidIdData
     *
     * @param mixed $value
     * @param NumericId $constraint
     */
    public function testValidate_withValidValue($value, NumericId $constraint)
    {
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @return array
     */
    public function getValidIdData()
    {
        return [
            [1, new NumericId()],
            [2, new NumericId(['checkType' => true])],
            ['3', new NumericId(['checkType' => false])],
            ['4', new NumericId()],
            [new StubValueWithToStringMethod('5'), new NumericId()],
        ];
    }

    /**
     * @return int
     */
    protected function getApiVersion()
    {
        return Validation::API_VERSION_2_5;
    }

    /**
     * @return NumericIdValidator
     */
    protected function createValidator()
    {
        return new NumericIdValidator();
    }
}
