<?php

namespace spec\Isolate\UnitOfWork\Object;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\ProtectedEntity;
use PhpSpec\Exception\Example\ExampleException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PropertyClonerSpec extends ObjectBehavior
{
    function it_throws_exception_when_target_or_source_are_not_valid_objects()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("cloneProperties", ["fakeEntity", new EntityFake()]);

        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("cloneProperties", [new EntityFake(), "fakeEntity"]);
    }

    function it_throws_exception_when_target_and_source_are_different_types()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be an instances of the same class."))
            ->during("cloneProperties", [new ProtectedEntity(), new EntityFake()]);
    }

    function it_clones_properties_from_source_into_target()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $sourceObject = new EntityFake(1, "Dawid", "Sajdak");

        $this->cloneProperties($object, $sourceObject);

        if ($object->getFirstName() !== "Dawid") {
            throw new ExampleException("Invalid object first name");
        }
        if ($object->getLastName() !== "Sajdak") {
            throw new ExampleException("Invalid object last name");
        }
    }

    function it_clone_single_property_from_source_into_target()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $sourceObject = new EntityFake(1, "Dawid", "Sajdak");

        $this->cloneProperty($object, $sourceObject, "firstName");

        if ($object->getFirstName() !== "Dawid") {
            throw new ExampleException("Invalid object first name");
        }
        if ($object->getLastName() !== "Orzechowicz") {
            throw new ExampleException("Invalid object last name");
        }
    }

    function it_throws_exception_when_property_does_not_exists()
    {
        $this->shouldThrow(new NotExistingPropertyException())
            ->during("cloneProperty", [new EntityFake(), new EntityFake(), "notExistingProperty"]);
    }
}
