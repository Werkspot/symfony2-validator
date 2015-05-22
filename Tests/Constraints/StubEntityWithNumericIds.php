<?php
namespace Werkspot\Component\Validator\Tests\Constraints;

use Werkspot\Component\Validator\Constraints as WerkspotAssert;

class StubEntityWithNumericIds
{
    /**
     * @WerkspotAssert\NumericId
     * @var mixed
     */
    private $numericIdWithoutTypeCheck;

    /**
     * @WerkspotAssert\NumericId(checkType=true)
     * @var mixed
     */
    private $numericIdWithTypeCheck;

    /**
     * @param mixed $numericIdWithoutTypeCheck
     * @param mixed $numericIdWithTypeCheck
     */
    public function __construct($numericIdWithoutTypeCheck, $numericIdWithTypeCheck)
    {
        $this->numericIdWithoutTypeCheck = $numericIdWithoutTypeCheck;
        $this->numericIdWithTypeCheck = $numericIdWithTypeCheck;
    }
}