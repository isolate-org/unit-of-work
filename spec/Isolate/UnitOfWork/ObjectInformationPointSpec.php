<?php

namespace spec\Isolate\UnitOfWork;

use Isolate\UnitOfWork\ObjectClass\Definition;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\ObjectClass\IdDefinition;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\PersistedEntityStub;
use Isolate\UnitOfWork\Tests\Double\NotPersistedEntityStub;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ObjectInformationPointSpec extends ObjectBehavior
{
    function it_throws_exception_when_constructed_with_non_traversable_class_definition_collection()
    {
        $this->shouldThrow(new InvalidArgumentException("Class definitions collection must be traversable."))
            ->during("__construct", ["test"]);
    }

    function it_throws_exception_when_created_with_invalid_class_definition_in_collection()
    {
        $this->shouldThrow(new InvalidArgumentException(
            "Each element of class definitions collection must be an instance of \\Isolate\\UnitOfWork\\ClassDefinition."
        ))->during("__construct", [["test"]]);
    }

    function it_throw_exception_during_verification_of_non_defined_object_class()
    {
        $this->shouldThrow(new RuntimeException("Class \"DateTime\" does not have definition."))
            ->during("isPersisted", [new \DateTime()]);
    }

    function it_throw_exception_on_attempt_of_getting_definition_for_non_defined_object_class()
    {
        $this->shouldThrow(new RuntimeException("Class \"DateTime\" does not have definition."))
            ->during("getDefinition", [new \DateTime()]);
    }

    function it_tells_that_object_is_persisted_when_it_has_not_empty_identity()
    {
        $this->beConstructedWith([
            new Definition(
                "\\Isolate\\UnitOfWork\\Tests\\Double\\PersistedEntityStub",
                new IdDefinition("id"),
                []
            )
        ]);

        $entity = new PersistedEntityStub();
        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_object_is_persisted_when_it_has_identity_equal_to_zero()
    {
        $this->beConstructedWith([
            new Definition(
                "\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake",
                new IdDefinition("id"),
                []
            )
        ]);

        $entity = new EntityFake(0);
        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_object_is_not_persisted_when_it_has_empty_identity()
    {
        $this->beConstructedWith([
            new Definition(
                "\\Isolate\\UnitOfWork\\Tests\\Double\\NotPersistedEntityStub",
                new IdDefinition("id"),
                []
            )
        ]);

        $entity = new NotPersistedEntityStub();
        $this->isPersisted($entity)->shouldReturn(false);
    }

    function it_throws_exception_during_persist_check_when_property_does_not_exists()
    {
        $this->beConstructedWith([
            new Definition(
                "\\Isolate\\UnitOfWork\\Tests\\Double\\PersistedEntityStub",
                new IdDefinition("not_exists"),
                []
            )
        ]);

        $entity = new PersistedEntityStub();
        $this->shouldThrow(
            new InvalidPropertyPathException("Cant access identifier in \"\\Isolate\\UnitOfWork\\Tests\\Double\\PersistedEntityStub\" using \"not_exists\" property path.")
        )->during("isPersisted", [$entity]);
    }

    function it_compare_two_equal_objects()
    {
        $this->beConstructedWith([
            new Definition(
                "\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake",
                new IdDefinition("id"),
                ["firstName"]
            )
        ]);

        $firstObject = new EntityFake(1);
        $secondObject = clone $firstObject;

        $this->isEqual($firstObject, $secondObject)->shouldReturn(true);
    }

    function it_compare_two_different_objects()
    {
        $this->beConstructedWith([
            new Definition(
                "\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake",
                new IdDefinition("id"),
                ["firstName"]
            )
        ]);

        $firstObject = new EntityFake(1);
        $secondObject = clone $firstObject;
        $secondObject->changeFirstName("new first name");

        $this->isEqual($firstObject, $secondObject)->shouldReturn(false);
    }

    function it_get_changes_between_objects()
    {
        $this->beConstructedWith([
            new Definition(
                "\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake",
                new IdDefinition("id"),
                ["firstName"]
            )
        ]);

        $firstObject = new EntityFake(1, "Norbert");
        $secondObject = clone $firstObject;
        $secondObject->changeFirstName("Michal");

        $this->getChanges($firstObject, $secondObject)->count()->shouldReturn(1);
        $this->getChanges($firstObject, $secondObject)->getChangeFor("firstName")->getOriginValue()->shouldReturn("Norbert");
    }
}
