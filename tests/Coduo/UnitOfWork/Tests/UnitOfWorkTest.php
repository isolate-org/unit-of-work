<?php

namespace Coduo\UnitOfWork\Tests;

use Coduo\UnitOfWork\ClassDefinition;
use Coduo\UnitOfWork\IdDefinition;
use Coduo\UnitOfWork\ObjectVerifier;
use Coduo\UnitOfWork\Tests\Double\NewCommandHandlerMock;
use Coduo\UnitOfWork\Tests\Double\NotPersistedEntityStub;
use Coduo\UnitOfWork\UnitOfWork;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
{
    function test_persisting_new_objects()
    {
        $classDefinition = new ClassDefinition(NotPersistedEntityStub::getClassName(), new IdDefinition("id"));
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

    /**
     * @param $classDefinitions
     * @return UnitOfWork
     */
    private function createUnitOfWork(array $classDefinitions = [])
    {
        return new UnitOfWork(new ObjectVerifier($classDefinitions));
    }
}
