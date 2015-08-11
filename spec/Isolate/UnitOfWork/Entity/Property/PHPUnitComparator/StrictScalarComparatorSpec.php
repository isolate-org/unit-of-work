<?php

namespace spec\Isolate\UnitOfWork\Entity\Property\PHPUnitComparator;

use PhpSpec\ObjectBehavior;
use SebastianBergmann\Comparator\ComparisonFailure;

class StrictScalarComparatorSpec extends ObjectBehavior
{
    function it_throws_exception_when_null_compared_to_zero()
    {
        $this->shouldThrow(ComparisonFailure::class)->during(
            "assertEquals", [0, null]
        );
    }
}