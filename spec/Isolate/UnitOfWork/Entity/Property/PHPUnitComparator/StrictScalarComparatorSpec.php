<?php

namespace spec\Isolate\UnitOfWork\Entity\Property\PHPUnitComparator;

use PhpSpec\ObjectBehavior;
use SebastianBergmann\Comparator\ComparisonFailure;

class StrictScalarComparatorSpec extends ObjectBehavior
{
    function it_throws_exception_when_null_compared_to_zero()
    {
        $exception = new ComparisonFailure(0, null, "0", "null", false, 'Failed asserting that null matches expected 0.');
        $this->shouldThrow($exception)->during(
            "assertEquals", [0, null]
        );
    }
}