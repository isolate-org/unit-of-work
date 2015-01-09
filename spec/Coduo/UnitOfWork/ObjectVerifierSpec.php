<?php

namespace spec\Coduo\UnitOfWork;

use Coduo\UnitOfWork\ClassDefinition;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\InvalidPropertyPathException;
use Coduo\UnitOfWork\Exception\RuntimeException;
use Coduo\UnitOfWork\IdDefinition;
use Coduo\UnitOfWork\Tests\Double\EditablePersistedEntityStub;
use Coduo\UnitOfWork\Tests\Double\PersistedEntityStub;
use Coduo\UnitOfWork\Tests\Double\NotPersistedEntityStub;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ObjectVerifierSpec extends ObjectBehavior
{
    function it_throws_exception_when_constructed_with_non_traversable_class_definition_collection()
    {
        $this->shouldThrow(new InvalidArgumentException("Class definitions collection must be traversable."))
            ->during("__construct", ["test"]);
    }

    function it_throws_exception_when_created_with_invalid_class_definition_in_collection()
    {
        $this->shouldThrow(new InvalidArgumentException(
            "Each element of class definitions collection must be an instance of \\Coduo\\UnitOfWork\\ClassDefinition."
        ))->during("__construct", [["test"]]);
    }

    function it_throw_exception_during_verification_of_non_defined_object_class()
    {
        $this->shouldThrow(new RuntimeException("Class \"DateTime\" does not have definition."))
            ->during("isPersisted", [new \DateTime()]);
    }

    function it_tells_that_object_is_persisted_when_it_has_not_empty_identity()
    {
        $this->beConstructedWith([
            new ClassDefinition("\\Coduo\\UnitOfWork\\Tests\\Double\\PersistedEntityStub", new IdDefinition("id"))
        ]);

        $entity = new PersistedEntityStub();
        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_object_is_not_persisted_when_it_has_empty_identity()
    {
        $this->beConstructedWith([
            new ClassDefinition("\\Coduo\\UnitOfWork\\Tests\\Double\\NotPersistedEntityStub", new IdDefinition("id"))
        ]);

        $entity = new NotPersistedEntityStub();
        $this->isPersisted($entity)->shouldReturn(false);
    }

    function it_throws_exception_during_persist_check_when_property_path_is_not_valid()
    {
        $this->beConstructedWith([
            new ClassDefinition("\\Coduo\\UnitOfWork\\Tests\\Double\\PersistedEntityStub", new IdDefinition("not_exists"))
        ]);

        $entity = new PersistedEntityStub();
        $this->shouldThrow(
            new InvalidPropertyPathException("Cant access identifier in \"\\Coduo\\UnitOfWork\\Tests\\Double\\PersistedEntityStub\" using \"not_exists\" property path.")
        )->during("isPersisted", [$entity]);
    }

    function it_compare_two_equal_objects()
    {
        $firstObject = new EditablePersistedEntityStub();
        $secondObject = clone $firstObject;

        $this->isEqual($firstObject, $secondObject)->shouldReturn(true);
    }

    function it_compare_two_different_objects()
    {
        $firstObject = new EditablePersistedEntityStub();
        $secondObject = clone $firstObject;
        $secondObject->changeName("changed name");

        $this->isEqual($firstObject, $secondObject)->shouldReturn(false);
    }
}
