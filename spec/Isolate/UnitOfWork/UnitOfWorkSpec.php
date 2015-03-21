<?php

namespace spec\Isolate\UnitOfWork;

use Isolate\UnitOfWork\CommandBus;
use Isolate\UnitOfWork\Entity\ChangeBuilder;
use Isolate\UnitOfWork\Entity\Comparer;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\Identifier;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Object\Registry;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UnitOfWorkSpec extends ObjectBehavior
{
    function let(Registry $registry, Identifier $identifier, Definition\Repository $definitions, Comparer $comparer, CommandBus $commandBus)
    {
        $this->beConstructedWith(
            $registry,
            $identifier,
            new ChangeBuilder($definitions->getWrappedObject(), $identifier->getWrappedObject()),
            $comparer,
            $commandBus
        );
    }

    function it_throw_exception_during_non_object_registration()
    {
        $this->shouldThrow(new InvalidArgumentException("Only objects can be registered in Unit of Work."))
            ->during("register", ["Coduo"]);
    }

    function it_throw_exception_during_non_entity_registration()
    {
        $this->shouldThrow(new InvalidArgumentException("Only entities can be registered in Unit of Work."))
            ->during("register", [new \stdClass()]);
    }

    function it_tells_when_entity_was_registered(Registry $registry, Identifier $identifier)
    {
        $entity = new EntityFake();
        $registry->isRegistered($entity)->willReturn(true);
        $identifier->isEntity($entity)->willReturn(true);
        $registry->register($entity)->willReturn();

        $this->register($entity);

        $this->isRegistered($entity)->shouldReturn(true);
    }
}
