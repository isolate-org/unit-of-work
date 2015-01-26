<?php

namespace Isolate\UnitOfWork\Tests;

use Isolate\UnitOfWork\Entity\ClassDefinition;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\IdDefinition;
use Isolate\UnitOfWork\Entity\InformationPoint;
use Isolate\UnitOfWork\Tests\Double\BatchEditCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\BatchNewCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\BatchRemoveCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\FailingBatchEditCommandHandlerStub;
use Isolate\UnitOfWork\UnitOfWork;
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

        $entities = $this->generateNewEntities(30);

        foreach ($entities as $entity) {
            $unitOfWork->register($entity);
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

        $entities = $this->generateNewEntities(41);

        foreach ($entities as $entity) {
            $unitOfWork->register($entity);
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

        $entities = $this->generateEditedObjects(41);

        foreach ($entities as $entity) {
            $unitOfWork->register($entity);
            $entity->changeFirstName("New");
            $entity->changeLastName("Name");
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

        $entities = $this->generateNewEntities(21);

        foreach ($entities as $entity) {
            $unitOfWork->register($entity);
            $unitOfWork->remove($entity);
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

        $entities = $this->generateEditedObjects(41);
        $originEntities = [];

        foreach ($entities as $entity) {
            $originEntities[] = clone($entity);
            $unitOfWork->register($entity);
            $entity->changeFirstName("New");
            $entity->changeLastName("Name");
        }

        $unitOfWork->commit();

        foreach ($entities as $index => $entity) {
            $this->assertEquals($entity, $originEntities[$index]);
        }

        $this->assertSame(1, $classDefinition->getEditCommandHandler()->getHandledBatchesCount());
        $this->assertSame(10, $classDefinition->getEditCommandHandler()->getTotalHandledCommandsCount());
    }

    private function generateNewEntities($count = 10)
    {
        $entities = [];
        $faker = Factory::create();
        for ($i = 0; $i < $count; $i++) {
            $entities[] = new EntityFake(null, $faker->firstName, $faker->lastName);
        }

        return $entities;
    }

    private function generateEditedObjects($count = 10)
    {
        $entities = [];
        $faker = Factory::create();
        for ($i = 0; $i < $count; $i++) {
            $entities[] = new EntityFake($faker->numberBetween(1, 10000), "Name", "Old");
        }

        return $entities;
    }

    /**
     * @return \Isolate\UnitOfWork\Entity\ClassDefinition
     */
    private function createClassDefinition()
    {
        return new ClassDefinition(
            new ClassName(EntityFake::getClassName()),
            new IdDefinition("id"),
            ["firstName", "lastName", "items"]
        );
    }

    /**
     * @param $classDefinitions
     * @return UnitOfWork
     */
    private function createUnitOfWork(array $classDefinitions = [])
    {
        return new UnitOfWork(new InformationPoint($classDefinitions), $this->eventDispatcher);
    }
}