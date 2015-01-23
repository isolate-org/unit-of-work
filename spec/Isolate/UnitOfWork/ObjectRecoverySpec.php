<?php

namespace spec\Isolate\UnitOfWork;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\PersistedEntityStub;
use PhpSpec\Exception\Example\ExampleException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ObjectRecoverySpec extends ObjectBehavior
{
    function it_throws_exception_when_at_least_one_of_compared_values_is_not_an_object()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("recover", ["fakeEntity", new EntityFake()]);

        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("recover", [new EntityFake(), "fakeEntity"]);
    }

    function it_throws_exception_when_compared_objects_have_different_classes()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be an instances of the same class."))
            ->during("recover", [new PersistedEntityStub(), new EntityFake()]);
    }

    function it_recover_object_property_values_using_class_definition()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $sourceObject = new EntityFake(1, "Dawid", "Sajdak");

        $this->recover($object, $sourceObject);

        if ($object->getFirstName() !== "Dawid") {
            throw new ExampleException("Invalid object first name");
        }
        if ($object->getLastName() !== "Sajdak") {
            throw new ExampleException("Invalid object last name");
        }
    }
}
