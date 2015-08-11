<?php

namespace Isolate\UnitOfWork\Entity\Property\PHPUnitComparator;

use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;

class StrictScalarComparator extends Comparator
{
    /**
     * @param mixed $expected
     * @param mixed $actual
     * @return bool
     */
    public function accepts($expected, $actual)
    {
        return ((is_scalar($expected) xor null === $expected) &&
            (is_scalar($actual) xor null === $actual))
        // allow comparison between strings and objects featuring __toString()
        || (is_string($expected) && is_object($actual) && method_exists($actual, '__toString'))
        || (is_object($expected) && method_exists($expected, '__toString') && is_string($actual));
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param float $delta
     * @param bool|false $canonicalize
     * @param bool|false $ignoreCase
     * @throws ComparisonFailure
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $expectedToCompare = $expected;
        $actualToCompare   = $actual;

        // always compare as strings to avoid strange behaviour
        // otherwise 0 == 'Foobar'
        if (is_string($expected) || is_string($actual)) {
            $expectedToCompare = (string) $expectedToCompare;
            $actualToCompare   = (string) $actualToCompare;

            if ($ignoreCase) {
                $expectedToCompare = strtolower($expectedToCompare);
                $actualToCompare   = strtolower($actualToCompare);
            }
        }

        if (is_null($expectedToCompare) && !is_bool($actual) || is_null($actual) && !is_bool($expectedToCompare)) {
            if ($expected !== $actual) {
                $this->throwComparisonFailureException($expected, $actual);
            }
        }

        if ($expectedToCompare != $actualToCompare) {
            if (is_string($expected) && is_string($actual)) {
                throw new ComparisonFailure(
                    $expected,
                    $actual,
                    $this->exporter->export($expected),
                    $this->exporter->export($actual),
                    false,
                    'Failed asserting that two strings are equal.'
                );
            }

            $this->throwComparisonFailureException($expected, $actual);
        }
    }

    /**
     * @param $expected
     * @param $actual
     */
    private function throwComparisonFailureException($expected, $actual)
    {
        throw new ComparisonFailure(
            $expected,
            $actual,
            $this->exporter->export($expected),
            $this->exporter->export($actual),
            false,
            sprintf(
                'Failed asserting that %s matches expected %s.',
                $this->exporter->export($actual),
                $this->exporter->export($expected)
            )
        );
    }
}
