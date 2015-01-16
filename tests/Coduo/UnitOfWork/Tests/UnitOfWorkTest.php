<?php

namespace Coduo\UnitOfWork\Tests;

use Coduo\UnitOfWork\Change;
use Coduo\UnitOfWork\ChangeSet;
use Coduo\UnitOfWork\ClassDefinition;
use Coduo\UnitOfWork\IdDefinition;
use Coduo\UnitOfWork\ObjectVerifier;
use Coduo\UnitOfWork\Tests\Double\EditCommandHandlerMock;
use Coduo\UnitOfWork\Tests\Double\EntityFake;
use Coduo\UnitOfWork\Tests\Double\FailingCommandHandlerStub;
use Coduo\UnitOfWork\Tests\Double\NewCommandHandlerMock;
use Coduo\UnitOfWork\Tests\Double\NotPersistedEntityStub;
use Coduo\UnitOfWork\Tests\Double\RemoveCommandHandlerMock;
use Coduo\UnitOfWork\UnitOfWork;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
{
    function test_commit_of_new_object()
    {
        $classDefinition = new ClassDefinition(
            NotPersistedEntityStub::getClassName(),
            new IdDefinition("id"),
            ["name"]
        );

        $classDefinition->addNewCommandHandler(new NewCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $object1 = new NotPersistedEntityStub();
        $object2 = new NotPersistedEntityStub();
        $unitOfWork->register($object1);
        $unitOfWork->register($object2);

        $unitOfWork->commit();

        $this->assertTrue($classDefinition->getNewCommandHandler()->objectWasPersisted($object1));
        $this->assertTrue($classDefinition->getNewCommandHandler()->objectWasPersisted($object2));
    }

    function test_commit_of_edited_and_persisted_object()
    {
        $classDefinition = new ClassDefinition(
            EntityFake::getClassName(),
            new IdDefinition("id"),
            ["firstName", "lastName"]
        );

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
        $classDefinition = new ClassDefinition(
            EntityFake::getClassName(),
            new IdDefinition("id"),
            ["firstName", "lastName"]
        );

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
        $classDefinition = new ClassDefinition(
            EntityFake::getClassName(),
            new IdDefinition("id"),
            ["firstName", "lastName"]
        );

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
        $classDefinition = new ClassDefinition(
            EntityFake::getClassName(),
            new IdDefinition("id"),
            ["firstName", "lastName"]
        );

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


    /**
     * @param $classDefinitions
     * @return UnitOfWork
     */
    private function createUnitOfWork(array $classDefinitions = [])
    {
        return new UnitOfWork(new ObjectVerifier($classDefinitions));
    }
}
