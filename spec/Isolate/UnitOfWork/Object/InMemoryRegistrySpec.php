<?php

namespace spec\Isolate\UnitOfWork\Object;

use Isolate\UnitOfWork\Object\RecoveryPoint;
use Isolate\UnitOfWork\Object\SnapshotMaker;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InMemoryRegistrySpec extends ObjectBehavior
{
    function let(SnapshotMaker $cloner, RecoveryPoint $recoveryPoint)
    {
        $cloner->makeSnapshotOf(Argument::type('object'))->will(function ($args) {
            $object = $args[0];
            return clone $object;
        });

        $this->beConstructedWith($cloner, $recoveryPoint);
    }

    function it_knows_when_object_was_not_registered()
    {
        $this->isRegistered(new EntityFake())->shouldReturn(false);
    }

    function it_knows_when_object_was_registered()
    {
        $object = new EntityFake();
        $this->register($object);
        $this->isRegistered($object)->shouldReturn(true);
    }

    function it_contains_snapshots_of_registered_objects()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $this->register($object);
        $object->changeFirstName("Dawid");
        $object->changeLastName("Sajdak");

        $snapshot = $this->getSnapshot($object);
        $snapshot->getFirstName()->shouldReturn("Norbert");
        $snapshot->getLastName()->shouldReturn("Orzechowicz");
    }

    function it_make_new_snapshots_of_registered_objects()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $this->register($object);
        $object->changeFirstName("Dawid");
        $object->changeLastName("Sajdak");

        $this->makeNewSnapshots();

        $snapshot = $this->getSnapshot($object);
        $snapshot->getFirstName()->shouldReturn("Dawid");
        $snapshot->getLastName()->shouldReturn("Sajdak");
    }

    function it_knows_when_object_should_not_be_removed()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $this->isRemoved($object)->shouldReturn(false);
    }

    function it_knows_when_object_should_be_removed()
    {
        $entity = new EntityFake(1, "Norbert", "Orzechowicz");
        $this->remove($entity);

        $this->isRemoved($entity)->shouldReturn(true);
    }

    function it_automatically_register_objects_that_should_be_removed()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->isRegistered($object)->shouldReturn(false);
        $this->remove($object);

        $this->isRegistered($object)->shouldReturn(true);
        $this->isRemoved($object)->shouldReturn(true);
    }

    function it_cleans_removed_objects()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $this->remove($object);

        $this->cleanRemoved();

        $this->isRegistered($object)->shouldReturn(false);
        $this->isRemoved($object)->shouldReturn(false);
    }

    function it_returns_all_objects_as_array()
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $this->register($object);

        $this->all()->shouldReturn([
            $object
        ]);
    }

    function it_resets_objects_to_states_from_snapshots(SnapshotMaker $cloner, RecoveryPoint $recoveryPoint)
    {
        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $objectSnapshot = new EntityFake(1, "Norbert", "Orzechowicz");
        $cloner->makeSnapshotOf($object)->willReturn($objectSnapshot);

        $this->register($object);
        $object->changeFirstName("Dawid");
        $object->changeLastName("Sajdak");
        $objectToRemove = new EntityFake(2);
        $this->remove($objectToRemove);
        $this->reset();

        $recoveryPoint->recover($object, $objectSnapshot)->shouldHaveBeenCalled();
        $this->isRemoved($objectToRemove)->shouldReturn(false);
    }
}
