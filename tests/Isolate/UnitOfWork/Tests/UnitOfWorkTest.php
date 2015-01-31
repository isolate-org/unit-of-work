<?php

namespace Isolate\UnitOfWork\Tests;

use Isolate\UnitOfWork\Entity\ChangeBuilder;
use Isolate\UnitOfWork\Entity\Property\ValueComparer;
use Isolate\UnitOfWork\Entity\Value\Change;
use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\Definition\Identity;
use Isolate\UnitOfWork\EntityStates;
use Isolate\UnitOfWork\Entity\InformationPoint;
use Isolate\UnitOfWork\Tests\Double\EditCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\FailingCommandHandlerStub;
use Isolate\UnitOfWork\Tests\Double\NewCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\RemoveCommandHandlerMock;
use Isolate\UnitOfWork\UnitOfWork;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
                new Change(new Property("firstName"), "Norbert", "Michal"),
                new Change(new Property("lastName"), "Orzechowicz", "Dabrowski")
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
            new ChangeSet([new Change(
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
        return new UnitOfWork(
            new InformationPoint([$this->createFakeEntityDefinition()]),
            new EventDispatcher()
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
        $definition->addNewCommandHandler($this->newCommandHandler);
        $definition->addEditCommandHandler($this->editCommandHandler);
        $definition->addRemoveCommandHandler($this->removeCommandHandler);

        return $definition;
    }
}
