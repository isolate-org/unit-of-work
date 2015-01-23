<?php

namespace spec\Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Change;
use Isolate\UnitOfWork\ChangeSet;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditCommandSpec extends ObjectBehavior
{
    function it_has_object_that_should_be_persisted()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $changes = new ChangeSet([new Change("Norbert", "Michal", "firstName")]);
        $this->beConstructedWith($object, $changes, 1);
        $this->getObject()->shouldReturn($object);
        $this->getChanges()->shouldReturn($changes);
    }

    function it_throws_exception_when_created_for_not_a_object_value()
    {
        $this->shouldThrow(new InvalidArgumentException("Edit command require object \"string\" type passed."))
            ->during("__construct", ["this is string", new ChangeSet(), 1]);
    }
}
