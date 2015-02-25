<?php

namespace spec\Isolate\UnitOfWork\Entity\Definition;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IdentitySpec extends ObjectBehavior
{
    function it_throw_exception_if_created_with_non_string_property_path()
    {
        $this->shouldThrow(new InvalidArgumentException("Property name must be a valid string."))
            ->during("__construct", [null]);
    }

    function it_throw_exception_if_created_with_empty_property_path()
    {
        $this->shouldThrow(new InvalidArgumentException("Property name can't be empty."))
            ->during("__construct", [""]);
    }

    function it_returns_property_path()
    {
        $this->beConstructedWith("id");
        $this->getPropertyName()->shouldReturn("id");
    }
}
