<?php

namespace spec\Isolate\UnitOfWork;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\PersistedEntityStub;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeBuilderSpec extends ObjectBehavior
{
    function it_returns_false_when_there_is_no_difference_between_same_property_in_two_objects()
    {
        $firstObject = $secondObject = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->isDifferent($firstObject, $secondObject, "firstName")->shouldReturn(false);
    }

    function it_returns_true_when_there_is_any_difference_between_same_property_in_two_objects()
    {
        $firstObject = new EntityFake(1, "Norbert");
        $secondObject = new EntityFake(1, "Michal");

        $this->isDifferent($firstObject, $secondObject, "firstName")->shouldReturn(true);;
    }

    function it_throws_exception_when_at_least_one_of_compared_values_is_not_an_object()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("isDifferent", ["fakeEntity", new EntityFake(), "firstName"]);

        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("isDifferent", [new EntityFake(), "fakeEntity", "firstName"]);
    }

    function it_throws_exception_when_compared_objects_have_different_classes()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be an instances of the same class."))
            ->during("isDifferent", [new PersistedEntityStub(), new EntityFake(), "firstName"]);

    }

    function it_throws_exception_when_property_does_not_exists()
    {
        $firstObject = $secondObject = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->shouldThrow(new NotExistingPropertyException("Property \"title\" does not exists in \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" class."))
            ->during("isDifferent", [$firstObject, $secondObject, "title"]);
    }

    function it_throws_exception_when_property_values_are_identical_in_both_objects()
    {
        $firstObject = $secondObject = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->shouldThrow(new RuntimeException("There are no differences between objects properties."))
            ->during("buildChange", [$firstObject, $secondObject, "firstName"]);
    }

    function it_build_change_for_different_objects()
    {
        $firstObject = new EntityFake(1, "Norbert");
        $secondObject = clone($firstObject);

        $secondObject->changeFirstName("Michal");

        $this->buildChange($firstObject, $secondObject, "firstName")->getOriginValue()->shouldReturn("Norbert");
    }
}

