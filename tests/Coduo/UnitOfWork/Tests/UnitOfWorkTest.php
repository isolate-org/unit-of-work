<?php

namespace Coduo\UnitOfWork\Tests;

use Coduo\UnitOfWork\Change;
use Coduo\UnitOfWork\ChangeSet;
use Coduo\UnitOfWork\Event\PostCommit;
use Coduo\UnitOfWork\Event\PreGetState;
use Coduo\UnitOfWork\Event\PreRegister;
use Coduo\UnitOfWork\Event\PreRemove;
use Coduo\UnitOfWork\Events;
use Coduo\UnitOfWork\ObjectClass\Definition;
use Coduo\UnitOfWork\ObjectClass\IdDefinition;
use Coduo\UnitOfWork\ObjectStates;
use Coduo\UnitOfWork\ObjectInformationPoint;
use Coduo\UnitOfWork\Tests\Double\EditCommandHandlerMock;
use Coduo\UnitOfWork\Tests\Double\EntityFake;
use Coduo\UnitOfWork\Tests\Double\FailingCommandHandlerStub;
use Coduo\UnitOfWork\Tests\Double\NewCommandHandlerMock;
use Coduo\UnitOfWork\Tests\Double\NotPersistedEntityStub;
use Coduo\UnitOfWork\Tests\Double\RemoveCommandHandlerMock;
use Coduo\UnitOfWork\UnitOfWork;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = new EventDispatcher();
    }

    function test_commit_of_new_object()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addNewCommandHandler(new NewCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake();
        $unitOfWork->register($object);

        $unitOfWork->commit();

        $this->assertTrue($classDefinition->getNewCommandHandler()->objectWasPersisted($object));
    }

    function test_commit_of_edited_and_persisted_object()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $unitOfWork->register($object);

        $object->changeFirstName("Michal");
        $object->changeLastName("Dabrowski");

        $unitOfWork->commit();

        $this->assertTrue($classDefinition->getEditCommandHandler()->objectWasPersisted($object));
        $this->assertEquals(
            new ChangeSet([new Change("Norbert", "Michal", "firstName"), new Change("Orzechowicz", "Dabrowski", "lastName")]),
            $classDefinition->getEditCommandHandler()->getPersistedObjectChanges($object)
        );
    }

    function test_commit_of_removed_and_persisted_object()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addRemoveCommandHandler(new RemoveCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(1, "Dawid", "Sajdak");

        $unitOfWork->register($object);
        $unitOfWork->remove($object);
        $unitOfWork->commit();

        $this->assertTrue($classDefinition->getRemoveCommandHandler()->objectWasPersisted($object));
        $this->assertFalse($unitOfWork->isRegistered($object));
    }

    function test_rollback_object_before_commit()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addRemoveCommandHandler(new RemoveCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(1, "Dawid", "Sajdak");
        $unitOfWork->register($object);

        $object->changeFirstName("Norbert");
        $object->changeLastName("Orzechowicz");

        $unitOfWork->rollback();

        $this->assertSame("Dawid", $object->getFirstName());
        $this->assertSame("Sajdak", $object->getLastName());
    }

    function test_rollback_when_command_handler_return_false()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new FailingCommandHandlerStub());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(1, "Dawid", "Sajdak");
        $unitOfWork->register($object);

        $object->changeFirstName("Norbert");
        $object->changeLastName("Orzechowicz");

        $unitOfWork->commit();

        $this->assertSame("Dawid", $object->getFirstName());
        $this->assertSame("Sajdak", $object->getLastName());
    }

    function test_that_rollback_after_successful_commit_have_no_affect_for_objects()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(1, "Dawid", "Sajdak");
        $unitOfWork->register($object);

        $object->changeFirstName("Norbert");
        $object->changeLastName("Orzechowicz");

        $this->assertSame(ObjectStates::EDITED_OBJECT, $unitOfWork->getObjectState($object));

        $unitOfWork->commit();
        $unitOfWork->rollback();

        $this->assertSame("Norbert", $object->getFirstName());
        $this->assertSame("Orzechowicz", $object->getLastName());
        $this->assertSame(ObjectStates::PERSISTED_OBJECT, $unitOfWork->getObjectState($object));
    }

    function test_state_of_registered_and_changed_object_that_does_not_have_id()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(null, "Dawid", "Sajdak");
        $unitOfWork->register($object);
        $object->changeFirstName("Norbert");

        $this->assertSame(ObjectStates::NEW_OBJECT, $unitOfWork->getObjectState($object));
    }

    function test_event_dispatching_during_successful_commit()
    {
        $preCommitEventDispatched = false;
        $postCommitEventDispatched = false;
        $this->eventDispatcher->addListener(Events::PRE_COMMIT, function(Event $event) use (&$preCommitEventDispatched) {
            $preCommitEventDispatched = true;
        });
        $this->eventDispatcher->addListener(Events::POST_COMMIT, function(PostCommit $event) use (&$postCommitEventDispatched) {
            $this->assertTrue($event->isSuccessful());
            $postCommitEventDispatched = true;
        });

        $classDefinition = $this->createFakeEntityDefinition();

        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $unitOfWork->register($object);

        $object->changeFirstName("Michal");
        $object->changeLastName("Dabrowski");

        $unitOfWork->commit();

        $this->assertTrue($preCommitEventDispatched);
        $this->assertTrue($postCommitEventDispatched);
    }

    function test_replacing_object_before_registration_in_unit_of_work()
    {
        $objectReplacement = new EntityFake(2, "Dawid", "Sajdak");
        $this->eventDispatcher->addListener(Events::PRE_REGISTER_OBJECT, function(PreRegister $event) use ($objectReplacement) {
            $event->replaceObject($objectReplacement);
        });

        $classDefinition = $this->createFakeEntityDefinition();

        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(1, "Norbert", "Orzechowicz");
        $unitOfWork->register($object);

        $this->assertFalse($unitOfWork->isRegistered($object));
        $this->assertTrue($unitOfWork->isRegistered($objectReplacement));
    }

    function test_replacing_object_before_checking_state()
    {
        $this->eventDispatcher->addListener(Events::PRE_GET_OBJECT_STATE, function(PreGetState $event) {
            $event->getObject()->setId(1);
        });

        $classDefinition = $this->createFakeEntityDefinition();

        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(null, "Norbert", "Orzechowicz");
        $unitOfWork->register($object);

        $this->assertSame(ObjectStates::PERSISTED_OBJECT, $unitOfWork->getObjectState($object));
    }

    function test_pre_remove_event()
    {
        $preRemoveTriggered = false;
        $this->eventDispatcher->addListener(Events::PRE_REMOVE_OBJECT, function(PreRemove $event) use (&$preRemoveTriggered) {
            $preRemoveTriggered = true;
        });

        $classDefinition = $this->createFakeEntityDefinition();

        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object = new EntityFake(null, "Norbert", "Orzechowicz");
        $unitOfWork->register($object);
        $unitOfWork->remove($object);

        $this->assertTrue($preRemoveTriggered);
    }

    /**
     * @param $classDefinitions
     * @return UnitOfWork
     */
    private function createUnitOfWork(array $classDefinitions = [])
    {
        return new UnitOfWork(new ObjectInformationPoint($classDefinitions), $this->eventDispatcher);
    }

    /**
     * @return Definition
     */
    private function createFakeEntityDefinition()
    {
        return new Definition(
            EntityFake::getClassName(),
            new IdDefinition("id"),
            ["firstName", "lastName"]
        );
    }
}
