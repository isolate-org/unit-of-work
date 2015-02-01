<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\EntityFakeChild;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InformationPointSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([]);
    }

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

    function it_throw_exception_during_verification_of_non_defined_entity()
    {
        $this->shouldThrow(new RuntimeException("Class \"DateTime\" does not have definition."))
            ->during("isPersisted", [new \DateTime()]);
    }

    function it_throw_exception_on_attempt_of_getting_definition_for_non_defined_entity()
    {
        $this->shouldThrow(new RuntimeException("Class \"DateTime\" does not have definition."))
            ->during("getDefinition", [new \DateTime()]);
    }

    function it_tells_that_entity_is_persisted_when_it_has_not_empty_identity()
    {
        $this->beConstructedWith(
            [new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"))]
        );

        $entity = new EntityFake(1);
        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_entity_is_persisted_when_it_has_identity_equal_to_zero()
    {
        $this->beConstructedWith(
            [new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"))]
        );

        $entity = new EntityFake(1);
        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_entity_is_not_persisted_when_it_has_empty_identity()
    {
        $this->beConstructedWith(
            [new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"))]
        );

        $entity = new EntityFake();
        $this->isPersisted($entity)->shouldReturn(false);
    }

    function it_throws_exception_during_persist_check_when_property_does_not_exists()
    {
        $this->beConstructedWith(
            [new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("not_exists"))]
        );

        $entity = new EntityFake(1);
        $this->shouldThrow(
            new InvalidPropertyPathException("Cant access identifier in \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" using \"not_exists\" property path.")
        )->during("isPersisted", [$entity]);
    }

    function it_throws_exception_when_associated_entity_is_not_defied()
    {
        $definition = new Definition(
            new ClassName(EntityFake::getClassName()),
            new Definition\Identity("not_exists")
        );
        $association = new Definition\Association(new ClassName(EntityFakeChild::getClassName()), Definition\Association::TO_MANY_ENTITIES);
        $definition->addToObserved(new Definition\Property("children", $association));

        $this->shouldThrow(
            new InvalidArgumentException("Entity class \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFakeChild\" used in association of \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" entity does not have definition.")
        )->during("__construct", [[$definition]]);
    }
}
