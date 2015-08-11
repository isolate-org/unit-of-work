<?php

namespace spec\Isolate\UnitOfWork\Entity\Property;

use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\ProtectedEntity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PHPUnitValueComparerSpec extends ObjectBehavior
{
    function it_returns_false_when_there_is_no_difference_between_same_property_in_two_objects()
    {
        $firstObject = $secondObject = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->hasDifferentValue(new Property("firstName"), $firstObject, $secondObject)->shouldReturn(false);
    }

    function it_returns_true_when_there_is_any_difference_between_same_property_in_two_objects()
    {
        $firstObject = new EntityFake(1, "Norbert");
        $secondObject = new EntityFake(1, "Michal");

        $this->hasDifferentValue(new Property("firstName"), $firstObject, $secondObject)->shouldReturn(true);
    }

    function it_returns_true_when_values_are_different_in_property_that_holds_array()
    {
        $firstObject = new EntityFake(1);
        $firstObject->setItems([new EntityFake(5), new EntityFake(6)]);

        $secondObject = new EntityFake(1);
        $secondObject->setItems([new EntityFake(5), new EntityFake(7)]);

        $this->hasDifferentValue(new Property("items"), $firstObject, $secondObject)->shouldReturn(true);
    }

    function it_throws_exception_when_at_least_one_of_compared_values_is_not_an_object()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("hasDifferentValue", [new Property("firstName"), "fakeEntity", new EntityFake()]);

        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("hasDifferentValue", [new Property("firstName"), new EntityFake(), "fakeEntity"]);
    }

    function it_throws_exception_when_compared_objects_have_different_classes()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be an instances of the same class."))
            ->during("hasDifferentValue", [new Property("firstName"), new ProtectedEntity(), new EntityFake()]);
    }

    function it_throws_exception_when_property_does_not_exists()
    {
        $firstObject = $secondObject = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->shouldThrow(new NotExistingPropertyException("Property \"title\" does not exists in \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" class."))
            ->during("hasDifferentValue", [new Property("title"), $firstObject, $secondObject]);
    }
}

