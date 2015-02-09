<?php

namespace Isolate\UnitOfWork\Object;

class InMemoryRegistry implements Registry
{
    /**
     * @var SnapshotMaker
     */
    private $snapshotMaker;

    /**
     * @var array
     */
    private $objects;

    /**
     * @var array
     */
    private $snapshots;

    /**
     * @var array
     */
    private $removed;

    /**
     * @var RecoveryPoint
     */
    private $recoveryPoint;

    /**
     * @param SnapshotMaker $snapshotMaker
     */
    public function __construct(SnapshotMaker $snapshotMaker, RecoveryPoint $recoveryPoint)
    {
        $this->snapshotMaker = $snapshotMaker;
        $this->objects = [];
        $this->snapshots = [];
        $this->removed = [];
        $this->recoveryPoint = $recoveryPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function isRegistered($object)
    {
        return array_key_exists($this->getId($object), $this->objects);
    }

    /**
     * {@inheritdoc}
     */
    public function register($object)
    {
        $this->objects[$this->getId($object)] = $object;
        $this->snapshots[$this->getId($object)] = $this->snapshotMaker->makeSnapshotOf($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getSnapshot($object)
    {
        return $this->snapshots[$this->getId($object)];
    }

    /**
     * {@inheritdoc}
     */
    public function makeNewSnapshots()
    {
        foreach ($this->objects as $id => $entity) {
            $this->snapshots[$id] = $this->snapshotMaker->makeSnapshotOf($entity);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRemoved($object)
    {
        return array_key_exists($this->getId($object), $this->removed);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        if (!$this->isRegistered($object)) {
            $this->register($object);
        }

        $this->removed[$this->getId($object)] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanRemoved()
    {
        foreach ($this->removed as $id => $object) {
            unset($this->snapshots[$id]);
            unset($this->objects[$id]);
        }

        $this->removed = [];
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return array_values($this->objects);
    }

    /**
     * Clean removed entities and restore registered from snapshots.
     */
    public function reset()
    {
        $this->removed = [];

        foreach ($this->snapshots as $id => $objectSnapshot) {
            $this->recoveryPoint->recover($this->objects[$id], $objectSnapshot);
        }
    }

    /**
     * @param $entity
     * @return string
     */
    private function getId($entity)
    {
        return spl_object_hash($entity);
    }
}
