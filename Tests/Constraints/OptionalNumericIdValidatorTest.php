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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Werkspot\Component\Validator\Constraints\OptionalNumericId;
use Werkspot\Component\Validator\Constraints\OptionalNumericIdValidator;

class OptionalNumericIdValidatorTest extends AbstractConstraintValidatorTest
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
     * @dataProvider getValidIdData
     *
     * @param mixed $value
     * @param OptionalNumericId $constraint
     */
    public function testValidate_withValidValue($value, OptionalNumericId $constraint)
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
            [1, new OptionalNumericId()],
            [2, new OptionalNumericId(['checkType' => true])],
            ['3', new OptionalNumericId(['checkType' => false])],
            ['4', new OptionalNumericId()],
            [new StubValueWithToStringMethod('5'), new OptionalNumericId()],
            [6.0, new OptionalNumericId()],
            [null, new OptionalNumericId()],
            [null, new OptionalNumericId(['checkType' => true])],
        ];
    }

    /**
    * @dataProvider getValidOptionalNumericIdEntityData
    *
    * @param mixed $optionalNumericIdWithoutTypeCheck
    * @param mixed $optionalNumericIdWithTypeCheck
    */
    public function testNumericIdEntityAnnotation_thatIsValid($optionalNumericIdWithoutTypeCheck, $optionalNumericIdWithTypeCheck)
    {
        $entity = new StubEntityWithOptionalNumericIds($optionalNumericIdWithoutTypeCheck, $optionalNumericIdWithTypeCheck);
        $violationList = $this->getValidatorWithAnnotationReader()->validate($entity);

        $this->assertEmpty($violationList);
    }

    /**
     * @return array
     */
    public function getValidOptionalNumericIdEntityData()
    {
        return [
            [1, 2],
            ['1', 2],
            [null, null],
        ];
    }

    /**
     * @dataProvider getInvalidOptionalNumericIdEntityData
     *
     * @param mixed $optionalNumericIdWithoutTypeCheck
     * @param mixed $optionalNumericIdWithTypeCheck
     * @param array $expectedPropertyPathAndValue
     */
    public function testNumericIdEntityAnnotation_thatIsInvalid($optionalNumericIdWithoutTypeCheck, $optionalNumericIdWithTypeCheck, array $expectedPropertyPathAndValue)
    {
        $entity = new StubEntityWithOptionalNumericIds($optionalNumericIdWithoutTypeCheck, $optionalNumericIdWithTypeCheck);
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
    public function getInvalidOptionalNumericIdEntityData()
    {
        return [
            [1, '2', ['optionalNumericIdWithTypeCheck' => '2']],
            ['1', 'a', ['optionalNumericIdWithTypeCheck' => 'a']],
            ['a', 'b', ['optionalNumericIdWithoutTypeCheck' => 'a', 'optionalNumericIdWithTypeCheck' => 'b']],
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
     * @return OptionalNumericIdValidator
     */
    protected function createValidator()
    {
        return new OptionalNumericIdValidator();
    }
}
