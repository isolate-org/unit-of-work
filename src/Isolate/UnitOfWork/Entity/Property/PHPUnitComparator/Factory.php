<?php

namespace Isolate\UnitOfWork\Entity\Property\PHPUnitComparator;

use SebastianBergmann\Comparator\ArrayComparator;
use SebastianBergmann\Comparator\DateTimeComparator;
use SebastianBergmann\Comparator\DOMNodeComparator;
use SebastianBergmann\Comparator\DoubleComparator;
use SebastianBergmann\Comparator\ExceptionComparator;
use SebastianBergmann\Comparator\Factory as BaseComparator;
use SebastianBergmann\Comparator\NumericComparator;
use SebastianBergmann\Comparator\ObjectComparator;
use SebastianBergmann\Comparator\ResourceComparator;
use SebastianBergmann\Comparator\SplObjectStorageComparator;
use SebastianBergmann\Comparator\TypeComparator;

final class Factory extends BaseComparator
{
    /**
     * Constructs a new factory.
     */
    public function __construct()
    {
        $this->register(new TypeComparator);
        $this->register(new StrictScalarComparator());
        $this->register(new NumericComparator);
        $this->register(new DoubleComparator);
        $this->register(new ArrayComparator);
        $this->register(new ResourceComparator);
        $this->register(new ObjectComparator);
        $this->register(new ExceptionComparator);
        $this->register(new SplObjectStorageComparator);
        $this->register(new DOMNodeComparator);
        $this->register(new DateTimeComparator);
    }
}