<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InformationPointSpec extends ObjectBehavior
{
    function let(Definition\Repository $definitions)
    {
        $this->beConstructedWith($definitions);
    }

    function it_tells_that_entity_is_persisted_when_it_has_not_empty_identity(Definition\Repository $definitions)
    {
        $definition = new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"));
        $entity = new EntityFake(1);
        $definitions->getDefinition($entity)->willReturn($definition);

        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_entity_is_persisted_when_it_has_identity_equal_to_zero(Definition\Repository $definitions)
    {
        $definition = new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"));
        $entity = new EntityFake(0);
        $definitions->getDefinition($entity)->willReturn($definition);

        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_entity_is_not_persisted_when_it_has_empty_identity(Definition\Repository $definitions)
    {
        $definition = new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"));
        $entity = new EntityFake();
        $definitions->getDefinition($entity)->willReturn($definition);
        $this->isPersisted($entity)->shouldReturn(false);
    }

    function it_throws_exception_during_persist_check_when_property_does_not_exists(Definition\Repository $definitions)
    {
        $definition = new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("not_exists"));
        $entity = new EntityFake();

        $definitions->getDefinition($entity)->willReturn($definition);

        $this->shouldThrow(
            new InvalidPropertyPathException("Cant access identifier in \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" using \"not_exists\" property path.")
        )->during("isPersisted", [$entity]);
    }
}
