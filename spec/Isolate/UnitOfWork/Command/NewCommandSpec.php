<?php

namespace spec\Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NewCommandSpec extends ObjectBehavior
{
    function it_has_entity_that_should_be_persisted()
    {
        $entity = new EntityFake();
        $this->beConstructedWith($entity);
        $this->getEntity()->shouldReturn($entity);
    }

    function it_throws_exception_when_created_for_not_a_object_value()
    {
        $this->shouldThrow(new InvalidArgumentException("New command require object \"string\" type passed."))
            ->during("__construct", ["this is string", 1]);
    }
}
