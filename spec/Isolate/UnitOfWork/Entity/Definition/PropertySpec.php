<?php

namespace spec\Isolate\UnitOfWork\Entity\Definition;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PropertySpec extends ObjectBehavior
{
    function it_throws_exception_if_created_with_non_string_or_empty_method_name()
    {
        $this->shouldThrow(new InvalidArgumentException("Property name can't be empty."))
            ->during("__construct", [""]);

        $this->shouldThrow(new InvalidArgumentException("Property name must be a valid string."))
            ->during("__construct", [new \stdClass()]);
    }
}
