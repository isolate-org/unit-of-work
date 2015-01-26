<?php

namespace Isolate\UnitOfWork\Tests;

use Isolate\UnitOfWork\Change;
use Isolate\UnitOfWork\ChangeSet;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Event\PostCommit;
use Isolate\UnitOfWork\Event\PreGetState;
use Isolate\UnitOfWork\Event\PreRegister;
use Isolate\UnitOfWork\Event\PreRemove;
use Isolate\UnitOfWork\Events;
use Isolate\UnitOfWork\Entity\ClassDefinition;
use Isolate\UnitOfWork\Entity\IdDefinition;
use Isolate\UnitOfWork\EntityStates;
use Isolate\UnitOfWork\Entity\InformationPoint;
use Isolate\UnitOfWork\Tests\Double\EditCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\FailingCommandHandlerStub;
use Isolate\UnitOfWork\Tests\Double\NewCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\RemoveCommandHandlerMock;
use Isolate\UnitOfWork\UnitOfWork;
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

    function test_commit_of_new_entity()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addNewCommandHandler(new NewCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake();
        $unitOfWork->register($entity);

        $unitOfWork->commit();

        $this->assertTrue($classDefinition->getNewCommandHandler()->entityWasPersisted($entity));
    }

    function test_commit_of_edited_and_persisted_entity()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(1, "Norbert", "Orzechowicz", [new EntityFake(2)]);
        $unitOfWork->register($entity);

        $entity->changeFirstName("Michal");
        $entity->changeLastName("Dabrowski");

        $unitOfWork->commit();

        $this->assertTrue($classDefinition->getEditCommandHandler()->entityWasPersisted($entity));
        $this->assertEquals(
            new ChangeSet([new Change("Norbert", "Michal", "firstName"), new Change("Orzechowicz", "Dabrowski", "lastName")]),
            $classDefinition->getEditCommandHandler()->getPersistedEntityChanges($entity)
        );
    }

    function test_commit_of_edited_and_persisted_entity_with_changes_in_property_that_contains_array()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(1, "Norbert", "Orzechowicz", [new EntityFake(2, "Dawid", "Sajdak")]);
        $unitOfWork->register($entity);

        $items = $entity->getItems();
        $items[0]->changeFirstName("Michal");
        $items[0]->changeLastName("Dabrowski");

        $unitOfWork->commit();

        $this->assertTrue($classDefinition->getEditCommandHandler()->entityWasPersisted($entity));
        $this->assertEquals(
            new ChangeSet([new Change(
                [new EntityFake(2, "Dawid", "Sajdak")],
                [new EntityFake(2, "Michal", "Dabrowski")],
                "items"
            )]),
            $classDefinition->getEditCommandHandler()->getPersistedEntityChanges($entity)
        );
    }

    function test_commit_of_removed_and_persisted_entity()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addRemoveCommandHandler(new RemoveCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(1, "Dawid", "Sajdak");

        $unitOfWork->register($entity);
        $unitOfWork->remove($entity);
        $unitOfWork->commit();

        $this->assertTrue($classDefinition->getRemoveCommandHandler()->entityWasRemoved($entity));
        $this->assertFalse($unitOfWork->isRegistered($entity));
    }

    function test_rollback_entity_before_commit()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addRemoveCommandHandler(new RemoveCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(1, "Dawid", "Sajdak");
        $unitOfWork->register($entity);

        $entity->changeFirstName("Norbert");
        $entity->changeLastName("Orzechowicz");

        $unitOfWork->rollback();

        $this->assertSame("Dawid", $entity->getFirstName());
        $this->assertSame("Sajdak", $entity->getLastName());
    }

    function test_rollback_when_command_handler_return_false()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new FailingCommandHandlerStub());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(1, "Dawid", "Sajdak");
        $unitOfWork->register($entity);

        $entity->changeFirstName("Norbert");
        $entity->changeLastName("Orzechowicz");

        $unitOfWork->commit();

        $this->assertSame("Dawid", $entity->getFirstName());
        $this->assertSame("Sajdak", $entity->getLastName());
    }

    function test_that_rollback_after_successful_commit_have_no_affect_for_entities()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(1, "Dawid", "Sajdak");
        $unitOfWork->register($entity);

        $entity->changeFirstName("Norbert");
        $entity->changeLastName("Orzechowicz");

        $this->assertSame(EntityStates::EDITED_ENTITY, $unitOfWork->getEntityState($entity));

        $unitOfWork->commit();
        $unitOfWork->rollback();

        $this->assertSame("Norbert", $entity->getFirstName());
        $this->assertSame("Orzechowicz", $entity->getLastName());
        $this->assertSame(EntityStates::PERSISTED_ENTITY, $unitOfWork->getEntityState($entity));
    }

    function test_state_of_registered_and_changed_entity_that_does_not_have_id()
    {
        $classDefinition = $this->createFakeEntityDefinition();
        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(null, "Dawid", "Sajdak");
        $unitOfWork->register($entity);
        $entity->changeFirstName("Norbert");

        $this->assertSame(EntityStates::NEW_ENTITY, $unitOfWork->getEntityState($entity));
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

        $entity = new EntityFake(1, "Norbert", "Orzechowicz");
        $unitOfWork->register($entity);

        $entity->changeFirstName("Michal");
        $entity->changeLastName("Dabrowski");

        $unitOfWork->commit();

        $this->assertTrue($preCommitEventDispatched);
        $this->assertTrue($postCommitEventDispatched);
    }

    function test_replacing_entity_before_registration_in_unit_of_work()
    {
        $entityReplacement = new EntityFake(2, "Dawid", "Sajdak");
        $this->eventDispatcher->addListener(Events::PRE_REGISTER_ENTITY, function(PreRegister $event) use ($entityReplacement) {
            $event->replaceEntity($entityReplacement);
        });

        $classDefinition = $this->createFakeEntityDefinition();

        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(1, "Norbert", "Orzechowicz");
        $unitOfWork->register($entity);

        $this->assertFalse($unitOfWork->isRegistered($entity));
        $this->assertTrue($unitOfWork->isRegistered($entityReplacement));
    }

    function test_replacing_entity_before_checking_state()
    {
        $this->eventDispatcher->addListener(Events::PRE_GET_ENTITY_STATE, function(PreGetState $event) {
            $event->getEntity()->setId(1);
        });

        $classDefinition = $this->createFakeEntityDefinition();

        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(null, "Norbert", "Orzechowicz");
        $unitOfWork->register($entity);

        $this->assertSame(EntityStates::PERSISTED_ENTITY, $unitOfWork->getEntityState($entity));
    }

    function test_pre_remove_event()
    {
        $preRemoveTriggered = false;
        $this->eventDispatcher->addListener(Events::PRE_REMOVE_ENTITY, function(PreRemove $event) use (&$preRemoveTriggered) {
            $preRemoveTriggered = true;
        });

        $classDefinition = $this->createFakeEntityDefinition();

        $classDefinition->addEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(null, "Norbert", "Orzechowicz");
        $unitOfWork->register($entity);
        $unitOfWork->remove($entity);

        $this->assertTrue($preRemoveTriggered);
    }

    /**
     * @param $classDefinitions
     * @return UnitOfWork
     */
    private function createUnitOfWork(array $classDefinitions = [])
    {
        return new UnitOfWork(new InformationPoint($classDefinitions), $this->eventDispatcher);
    }

    /**
     * @return \Isolate\UnitOfWork\Entity\ClassDefinition
     */
    private function createFakeEntityDefinition()
    {
        return new ClassDefinition(
            new ClassName(EntityFake::getClassName()),
            new IdDefinition("id"),
            ["firstName", "lastName", "items"]
        );
    }
}
