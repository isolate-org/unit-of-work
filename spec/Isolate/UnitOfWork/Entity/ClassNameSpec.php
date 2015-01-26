<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClassNameSpec extends ObjectBehavior
{
    function it_throws_exception_if_created_with_non_string_class_name()
    {
        $this->shouldThrow(new InvalidArgumentException("Class name must be a valid string."))
            ->during("__construct", [new \DateTime()]);
    }

    function it_throws_exception_if_class_not_exists()
    {
        $this->shouldThrow(new InvalidArgumentException("Class \"Coduo\" does not exists."))
            ->during("__construct", ["Coduo"]);
    }
}
