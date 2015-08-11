<?php

namespace Isolate\UnitOfWork\Object;

interface Registry
{
    /**
     * @param $object
     * @return bool
     */
    public function isRegistered($object);

    /**
     * @param $object
     */
    public function register($object);

    /**
     * @param $object
     * @return mixed
     */
    public function getSnapshot($object);

    /**
     * Make new snapshots for all registered objects
     */
    public function makeNewSnapshots();

    /**
     * @param mixed $object
     * @return bool
     */
    public function isRemoved($object);

    /**
     * Marks object as "removed"
     *
     * @param mixed $object
     */
    public function remove($object);

    /**
     * Cleans all objects marked as removed
     */
    public function cleanRemoved();

    /**
     * @return array
     */
    public function all();

    /**
     * Restore object states from their snapshots.
     */
    public function reset();
}
