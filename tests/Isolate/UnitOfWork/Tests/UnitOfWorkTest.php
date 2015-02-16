<?php

namespace Isolate\UnitOfWork\Tests;

use Isolate\UnitOfWork\CommandBus\SilentBus;
use Isolate\UnitOfWork\Entity\ChangeBuilder;
use Isolate\UnitOfWork\Entity\Comparer;
use Isolate\UnitOfWork\Entity\Identifier\Symfony\PropertyAccessorIdentifier;
use Isolate\UnitOfWork\Entity\Value\Change\ScalarChange;
use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\Definition\Identity;
use Isolate\UnitOfWork\EntityStates;
use Isolate\UnitOfWork\Object\InMemoryRegistry;
use Isolate\UnitOfWork\Object\RecoveryPoint;
use Isolate\UnitOfWork\Object\SnapshotMaker\Adapter\DeepCopy\SnapshotMaker;
use Isolate\UnitOfWork\Tests\Double\EditCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\FailingCommandHandlerStub;
use Isolate\UnitOfWork\Tests\Double\NewCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\RemoveCommandHandlerMock;
use Isolate\UnitOfWork\UnitOfWork;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EditCommandHandlerMock
     */
    private $editCommandHandler;

    /**
     * @var NewCommandHandlerMock
     */
    private $newCommandHandler;

    /**
     * @var RemoveCommandHandlerMock
     */
    private $removeCommandHandler;

    function setUp()
    {
        $this->editCommandHandler = new EditCommandHandlerMock();
        $this->newCommandHandler = new NewCommandHandlerMock();
        $this->removeCommandHandler = new RemoveCommandHandlerMock();
    }

    function test_commit_of_new_entity()
    {
        $unitOfWork = $this->createUnitOfWork();

        $entity = new EntityFake();
        $unitOfWork->register($entity);

        $this->assertSame(EntityStates::NEW_ENTITY, $unitOfWork->getEntityState($entity));
        $unitOfWork->commit();
        $this->assertTrue($this->newCommandHandler->entityWasPersisted($entity));
    }

    function test_commit_of_edited_and_persisted_entity()
    {
        $unitOfWork = $this->createUnitOfWork();

        $entity = new EntityFake(1, "Norbert", "Orzechowicz", [new EntityFake(2)]);
        $unitOfWork->register($entity);

        $entity->changeFirstName("Michal");
        $entity->changeLastName("Dabrowski");

        $unitOfWork->commit();

        $this->assertTrue($this->editCommandHandler->entityWasPersisted($entity));
        $this->assertEquals(
            new ChangeSet([
                new ScalarChange(new Property("firstName"), "Norbert", "Michal"),
                new ScalarChange(new Property("lastName"), "Orzechowicz", "Dabrowski")
            ]),
            $this->editCommandHandler->getPersistedEntityChanges($entity)
        );
    }

    function test_commit_of_edited_and_persisted_entity_with_changes_in_property_that_contains_array()
    {
        $unitOfWork = $this->createUnitOfWork();

        $entity = new EntityFake(1, "Norbert", "Orzechowicz", [new EntityFake(2, "Dawid", "Sajdak")]);
        $unitOfWork->register($entity);

        $items = $entity->getItems();
        $items[0]->changeFirstName("Michal");
        $items[0]->changeLastName("Dabrowski");

        $unitOfWork->commit();

        $this->assertTrue($this->editCommandHandler->entityWasPersisted($entity));
        $this->assertEquals(
            new ChangeSet([new ScalarChange(
                new Property("items"),
                [new EntityFake(2, "Dawid", "Sajdak")],
                [new EntityFake(2, "Michal", "Dabrowski")]
            )]),
            $this->editCommandHandler->getPersistedEntityChanges($entity)
        );
    }

    function test_commit_of_removed_and_persisted_entity()
    {
        $unitOfWork = $this->createUnitOfWork();

        $entity = new EntityFake(1, "Dawid", "Sajdak");

        $unitOfWork->register($entity);
        $unitOfWork->remove($entity);
        $unitOfWork->commit();

        $this->assertTrue($this->removeCommandHandler->entityWasRemoved($entity));
        $this->assertFalse($unitOfWork->isRegistered($entity));
    }

    function test_commits_after_persist_and_update_entity()
    {
        $unitOfWork = $this->createUnitOfWork();

        $entity = new EntityFake();
        $unitOfWork->register($entity);

        $this->assertSame(EntityStates::NEW_ENTITY, $unitOfWork->getEntityState($entity));
        $unitOfWork->commit();

        $this->assertTrue($this->newCommandHandler->entityWasPersisted($entity));

        $entity->changeFirstName('Norbert');
        $unitOfWork->commit();

        $this->assertTrue($this->editCommandHandler->entityWasPersisted($entity));
    }

    function test_rollback_entity_before_commit()
    {
        $unitOfWork = $this->createUnitOfWork();

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
        $this->editCommandHandler = new FailingCommandHandlerStub();
        $unitOfWork = $this->createUnitOfWork();

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
        $unitOfWork = $this->createUnitOfWork();

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

    /**
     * @return UnitOfWork
     */
    private function createUnitOfWork()
    {
        $definitions = new Definition\Repository\InMemory([$this->createFakeEntityDefinition()]);
        $identifier = new PropertyAccessorIdentifier($definitions);

        return new UnitOfWork(
            new InMemoryRegistry(new SnapshotMaker(), new RecoveryPoint()),
            $identifier,
            new ChangeBuilder($definitions, $identifier),
            new Comparer($definitions),
            new SilentBus($definitions)
        );
    }

    /**
     * @return \Isolate\UnitOfWork\Entity\Definition
     */
    private function createFakeEntityDefinition()
    {
        $definition = new Definition(new ClassName(EntityFake::getClassName()), new Identity("id"));
        $definition->setObserved([
            new Property("firstName"),
            new Property("lastName"),
            new Property("items")]
        );
        $definition->setNewCommandHandler($this->newCommandHandler);
        $definition->setEditCommandHandler($this->editCommandHandler);
        $definition->setRemoveCommandHandler($this->removeCommandHandler);

        return $definition;
    }
}
