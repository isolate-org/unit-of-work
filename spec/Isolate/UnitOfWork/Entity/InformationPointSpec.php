<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InformationPointSpec extends ObjectBehavior
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
        $this->beConstructedWith([
            new Definition(
                new ClassName(EntityFake::getClassName()),
                new Definition\Identity("id")
            )
        ]);

        $entity = new EntityFake(1);
        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_entity_is_persisted_when_it_has_identity_equal_to_zero()
    {
        $this->beConstructedWith([
            new Definition(
                new ClassName(EntityFake::getClassName()),
                new Definition\Identity("id")
            )
        ]);

        $entity = new EntityFake(1);
        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_entity_is_not_persisted_when_it_has_empty_identity()
    {
        $this->beConstructedWith([
            new Definition(
                new ClassName(EntityFake::getClassName()),
                new Definition\Identity("id")
            )
        ]);

        $entity = new EntityFake();
        $this->isPersisted($entity)->shouldReturn(false);
    }

    function it_throws_exception_during_persist_check_when_property_does_not_exists()
    {
        $this->beConstructedWith([
            new Definition(
                new ClassName(EntityFake::getClassName()),
                new Definition\Identity("not_exists")
            )
        ]);

        $entity = new EntityFake(1);
        $this->shouldThrow(
            new InvalidPropertyPathException("Cant access identifier in \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" using \"not_exists\" property path.")
        )->during("isPersisted", [$entity]);
    }

    function it_compare_two_equal_entities()
    {
        $definition = new Definition(
            new ClassName(EntityFake::getClassName()),
            new Definition\Identity("id")
        );
        $definition->addToObserved(new Definition\Property("firstName"));

        $this->beConstructedWith([$definition]);

        $firstEntity = new EntityFake(1);
        $secondEntity = clone $firstEntity;

        $this->areEqual($firstEntity, $secondEntity)->shouldReturn(true);
    }

    function it_compare_two_different_entities()
    {
        $definition = new Definition(
            new ClassName(EntityFake::getClassName()),
            new Definition\Identity("id")
        );
        $definition->addToObserved(new Definition\Property("firstName"));

        $this->beConstructedWith([$definition]);

        $firstEntity = new EntityFake(1);
        $secondEntity = clone $firstEntity;
        $secondEntity->changeFirstName("new first name");

        $this->areEqual($firstEntity, $secondEntity)->shouldReturn(false);
    }

    function it_get_changes_between_entities()
    {
        $definition = new Definition(
            new ClassName(EntityFake::getClassName()),
            new Definition\Identity("id")
        );
        $definition->addToObserved(new Definition\Property("firstName"));

        $this->beConstructedWith([$definition]);

        $firstEntity = new EntityFake(1, "Norbert");
        $secondEntity = clone $firstEntity;
        $secondEntity->changeFirstName("Michal");

        $this->getChanges($firstEntity, $secondEntity)->count()->shouldReturn(1);
        $this->getChanges($firstEntity, $secondEntity)->getChangeFor("firstName")->getOriginValue()->shouldReturn("Norbert");
    }
}
