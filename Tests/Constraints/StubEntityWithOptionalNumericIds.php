<?php
namespace Werkspot\Component\Validator\Tests\Constraints;

use Werkspot\Component\Validator\Constraints as WerkspotAssert;

class StubEntityWithOptionalNumericIds
{
    /**
     * @WerkspotAssert\OptionalNumericId
     * @var mixed
     */
    private $optionalNumericIdWithoutTypeCheck;

    /**
     * @WerkspotAssert\OptionalNumericId(checkType=true)
     * @var mixed
     */
    private $optionalNumericIdWithTypeCheck;

    /**
     * @param mixed $optionalNumericIdWithoutTypeCheck
     * @param mixed $optionalNumericIdWithTypeCheck
     */
    public function __construct(
        $optionalNumericIdWithoutTypeCheck,
        $optionalNumericIdWithTypeCheck
    ) {
        $this->optionalNumericIdWithoutTypeCheck = $optionalNumericIdWithoutTypeCheck;
        $this->optionalNumericIdWithTypeCheck = $optionalNumericIdWithTypeCheck;
    }
}