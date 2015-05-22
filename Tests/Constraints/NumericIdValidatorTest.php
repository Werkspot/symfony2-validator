<?php
namespace Werkspot\Component\Validator\Tests\Constraints;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorBuilder;
use Symfony\Component\Validator\ValidatorInterface;
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
     * @dataProvider getValidNumericIdEntityData
     *
     * @param mixed $numericIdWithoutTypeCheck
     * @param mixed $numericIdWithTypeCheck
     */
    public function testNumericIdEntityAnnotation_thatIsValid($numericIdWithoutTypeCheck, $numericIdWithTypeCheck)
    {
        $entity = new StubEntityWithNumericIds($numericIdWithoutTypeCheck, $numericIdWithTypeCheck);
        $violationList = $this->getValidatorWithAnnotationReader()->validate($entity);

        $this->assertEmpty($violationList);
    }

    /**
     * @return array
     */
    public function getValidNumericIdEntityData()
    {
        return [
            [1, 2],
            ['1', 2],
        ];
    }

    /**
     * @dataProvider getInvalidNumericIdEntityData
     *
     * @param mixed $numericIdWithoutTypeCheck
     * @param mixed $numericIdWithTypeCheck
     */
    public function testNumericIdEntityAnnotation_thatIsInvalid($numericIdWithoutTypeCheck, $numericIdWithTypeCheck, array $expectedPropertyPathAndValue)
    {
        $entity = new StubEntityWithNumericIds($numericIdWithoutTypeCheck, $numericIdWithTypeCheck);
        $violationList = $this->getValidatorWithAnnotationReader()->validate($entity);

        $expectedMessage = 'This id is not valid.';

        $this->assertCount(count($expectedPropertyPathAndValue), $violationList);
        foreach ($expectedPropertyPathAndValue as $expectedPropertyPath => $expectedValue) {
            $this->assertViolationListContainsMessage($violationList, $expectedPropertyPath, $expectedMessage, $expectedValue);
        }
    }

    /**
     * @return array
     */
    public function getInvalidNumericIdEntityData()
    {
        return [
            [1, '2', ['numericIdWithTypeCheck' => '2']],
            ['1', 'a', ['numericIdWithTypeCheck' => 'a']],
            ['a', 'b', ['numericIdWithoutTypeCheck' => 'a', 'numericIdWithTypeCheck' => 'b']],
        ];
    }

    /**
     * @param ConstraintViolationListInterface|ConstraintViolationInterface[] $violationList
     * @param string $expectedPropertyPath
     * @param string $expectedMessage
     * @param mixed $expectedValidValue
     */
    private function assertViolationListContainsMessage(ConstraintViolationListInterface $violationList, $expectedPropertyPath, $expectedMessage, $expectedValidValue)
    {
        foreach ($violationList as $violation) {
            if (
                $violation->getPropertyPath() == $expectedPropertyPath &&
                $violation->getMessage() == $expectedMessage &&
                $violation->getInvalidValue() == $expectedValidValue
            ) {
                return;
            }
        }

        $this->fail(sprintf('Message [%s] not found in ConstraintViolationList', $expectedMessage));
    }

    /**
     * @return ValidatorInterface
     */
    private function getValidatorWithAnnotationReader()
    {
        $validator = new ValidatorBuilder();
        $validator->enableAnnotationMapping($this->getAnnotationReader());

        return $validator->getValidator();
    }

    /**
     * @return CachedReader
     */
    private function getAnnotationReader()
    {
        return new CachedReader(new AnnotationReader(), new ArrayCache());
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
