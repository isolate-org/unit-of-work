<?php

namespace spec\Isolate\UnitOfWork\Entity\Definition\Repository;

use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\EntityFakeChild;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InMemorySpec extends ObjectBehavior
{
    function it_is_entity_definition_repository()
    {
        $this->shouldImplement('Isolate\UnitOfWork\Entity\Definition\Repository');
    }

    function it_throws_exception_when_crated_with_non_traversable_collection()
    {
        $this->shouldThrow(
            new InvalidArgumentException("Entity definition repository require array od traversable collection of entity definitions.")
        )->during("__construct", [new \stdClass()]);
    }

    function it_throws_exception_when_crated_with_non_entity_definition_in_collection()
    {
        $this->shouldThrow(
            new InvalidArgumentException("Each element in collection needs to be an instance of Isolate\\UnitOfWork\\Entity\\Definition")
        )->during("__construct", [[new \stdClass()]]);
    }

    function it_throws_exception_when_checking_if_definition_exists_for_non_object()
    {
        $this->shouldThrow(
            new InvalidArgumentException("Entity definition repository require objects as arguments for methods.")
        )->during("hasDefinition", ["string"]);
    }

    function it_knows_when_entity_definition_exists()
    {
        $definition = new Definition(
            new ClassName(EntityFake::getClassName()),
            new Definition\Identity("id")
        );

        $this->beConstructedWith([$definition]);

        $entity = new EntityFake();

        $this->hasDefinition($entity)->shouldReturn(true);
    }

    function it_throws_exception_when_there_is_no_definition_for_entity()
    {
        $entity = new EntityFake();

        $this->shouldThrow(
            new RuntimeException(sprintf("Entity definition for \"%s\" does not exists.", EntityFake::getClassName()))
        )->during("getDefinition", [$entity]);
    }

    function it_returns_definition_for_entity()
    {
        $definition = new Definition(
            new ClassName(EntityFake::getClassName()),
            new Definition\Identity("id")
        );
        $this->beConstructedWith([$definition]);

        $entity = new EntityFake();

        $this->getDefinition($entity)->shouldReturn($definition);
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
