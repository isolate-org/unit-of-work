<?php

namespace Isolate\UnitOfWork\Tests;

use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Comparer;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Event\PostCommit;
use Isolate\UnitOfWork\Event\PreGetState;
use Isolate\UnitOfWork\Event\PreRegister;
use Isolate\UnitOfWork\Event\PreRemove;
use Isolate\UnitOfWork\Events;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\Definition\Identity;
use Isolate\UnitOfWork\EntityStates;
use Isolate\UnitOfWork\Entity\InformationPoint;
use Isolate\UnitOfWork\Object\InMemoryRegistry;
use Isolate\UnitOfWork\Object\RecoveryPoint;
use Isolate\UnitOfWork\Object\SnapshotMaker\Adapter\DeepCopy\SnapshotMaker;
use Isolate\UnitOfWork\Tests\Double\EditCommandHandlerMock;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\UnitOfWork;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class UnitOfWorkEventsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = new EventDispatcher();
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

        $classDefinition->setEditCommandHandler(new EditCommandHandlerMock());
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

        $classDefinition->setEditCommandHandler(new EditCommandHandlerMock());
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

        $classDefinition->setEditCommandHandler(new EditCommandHandlerMock());
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

        $classDefinition->setEditCommandHandler(new EditCommandHandlerMock());
        $unitOfWork = $this->createUnitOfWork([
            $classDefinition
        ]);

        $entity = new EntityFake(null, "Norbert", "Orzechowicz");
        $unitOfWork->register($entity);
        $unitOfWork->remove($entity);

        $this->assertTrue($preRemoveTriggered);
    }

    /**
     * @return UnitOfWork
     */
    private function createUnitOfWork()
    {
        return new UnitOfWork(
            new InMemoryRegistry(new SnapshotMaker(), new RecoveryPoint()),
            new InformationPoint([$this->createFakeEntityDefinition()]),
            new Comparer(),
            $this->eventDispatcher
        );
    }

    /**
     * @return \Isolate\UnitOfWork\Entity\Definition
     */
    private function createFakeEntityDefinition()
    {
        $definition =  new Definition(new ClassName(EntityFake::getClassName()), new Identity("id"));
        $definition->setObserved([
                new Property("firstName"),
                new Property("lastName"),
                new Property("items")]
        );

        return $definition;
    }
}
