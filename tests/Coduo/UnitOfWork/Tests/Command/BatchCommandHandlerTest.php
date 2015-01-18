<?php

namespace Coduo\UnitOfWork\Tests;

use Coduo\UnitOfWork\ObjectClass\Definition;
use Coduo\UnitOfWork\ObjectClass\IdDefinition;
use Coduo\UnitOfWork\ObjectInformationPoint;
use Coduo\UnitOfWork\Tests\Double\BatchEditCommandHandlerMock;
use Coduo\UnitOfWork\Tests\Double\BatchNewCommandHandlerMock;
use Coduo\UnitOfWork\Tests\Double\BatchRemoveCommandHandlerMock;
use Coduo\UnitOfWork\Tests\Double\EntityFake;
use Coduo\UnitOfWork\Tests\Double\FailingBatchEditCommandHandlerStub;
use Coduo\UnitOfWork\UnitOfWork;
use Faker\Factory;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BatchCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = new EventDispatcher();
    }

    function test_handling_batch_of_new_commands()
    {
        $classDefinition = $this->createClassDefinition();
        $classDefinition->addNewCommandHandler(new BatchNewCommandHandlerMock());

        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $objects = $this->generateNewObjects(30);

        foreach ($objects as $object) {
            $unitOfWork->register($object);
        }

        $unitOfWork->commit();

        $this->assertSame(3, $classDefinition->getNewCommandHandler()->getHandledBatchesCount());
        $this->assertSame(30, $classDefinition->getNewCommandHandler()->getTotalHandledCommandsCount());
    }

    function test_handling_batch_of_new_commands_even_when_last_batch_is_not_complete()
    {
        $classDefinition = $this->createClassDefinition();
        $classDefinition->addNewCommandHandler(new BatchNewCommandHandlerMock());

        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $objects = $this->generateNewObjects(41);

        foreach ($objects as $object) {
            $unitOfWork->register($object);
        }

        $unitOfWork->commit();

        $this->assertSame(5, $classDefinition->getNewCommandHandler()->getHandledBatchesCount());
        $this->assertSame(41, $classDefinition->getNewCommandHandler()->getTotalHandledCommandsCount());
    }

    function test_handling_batch_of_edit_commands_even_when_last_batch_is_not_complete()
    {
        $classDefinition = $this->createClassDefinition();
        $classDefinition->addEditCommandHandler(new BatchEditCommandHandlerMock());

        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $objects = $this->generateEditedObjects(41);

        foreach ($objects as $object) {
            $unitOfWork->register($object);
            $object->changeFirstName("New");
            $object->changeLastName("Name");
        }

        $unitOfWork->commit();

        $this->assertSame(5, $classDefinition->getEditCommandHandler()->getHandledBatchesCount());
        $this->assertSame(41, $classDefinition->getEditCommandHandler()->getTotalHandledCommandsCount());
    }

    function test_handling_batch_of_remove_commands_even_when_last_batch_is_not_complete()
    {
        $classDefinition = $this->createClassDefinition();
        $classDefinition->addRemoveCommandHandler(new BatchRemoveCommandHandlerMock());

        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $objects = $this->generateNewObjects(21);

        foreach ($objects as $object) {
            $unitOfWork->register($object);
            $unitOfWork->remove($object);
        }

        $unitOfWork->commit();

        $this->assertSame(3, $classDefinition->getRemoveCommandHandler()->getHandledBatchesCount());
        $this->assertSame(21, $classDefinition->getRemoveCommandHandler()->getTotalHandledCommandsCount());
    }

    function test_handling_batch_of_edit_commands_that_fails()
    {
        $classDefinition = $this->createClassDefinition();
        $classDefinition->addEditCommandHandler(new FailingBatchEditCommandHandlerStub());

        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $objects = $this->generateEditedObjects(41);
        $originObjects = [];

        foreach ($objects as $object) {
            $originObjects[] = clone($object);
            $unitOfWork->register($object);
            $object->changeFirstName("New");
            $object->changeLastName("Name");
        }

        $unitOfWork->commit();

        foreach ($objects as $index => $object) {
            $this->assertEquals($object, $originObjects[$index]);
        }

        $this->assertSame(1, $classDefinition->getEditCommandHandler()->getHandledBatchesCount());
        $this->assertSame(10, $classDefinition->getEditCommandHandler()->getTotalHandledCommandsCount());
    }

    private function generateNewObjects($count = 10)
    {
        $objects = [];
        $faker = Factory::create();
        for ($i = 0; $i < $count; $i++) {
            $objects[] = new EntityFake(null, $faker->firstName, $faker->lastName);
        }

        return $objects;
    }

    private function generateEditedObjects($count = 10)
    {
        $objects = [];
        $faker = Factory::create();
        for ($i = 0; $i < $count; $i++) {
            $objects[] = new EntityFake($faker->numberBetween(1, 10000), "Name", "Old");
        }

        return $objects;
    }

    /**
     * @return Definition
     */
    private function createClassDefinition()
    {
        return new Definition(
            EntityFake::getClassName(),
            new IdDefinition("id"),
            ["firstName", "lastName"]
        );
    }

    /**
     * @param $classDefinitions
     * @return UnitOfWork
     */
    private function createUnitOfWork(array $classDefinitions = [])
    {
        return new UnitOfWork(new ObjectInformationPoint($classDefinitions), $this->eventDispatcher);
    }
}
