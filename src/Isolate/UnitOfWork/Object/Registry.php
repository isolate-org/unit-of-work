<?php

namespace Isolate\UnitOfWork\Object;

/**
 * @api
 */
interface Registry
{
    /**
     * @param $object
     * @return bool
     * 
     * @api
     */
    public function isRegistered($object);

    /**
     * @param $object
     * 
     * @api
     */
    public function register($object);

    /**
     * @param $object
     * @return mixed
     * 
     * @api
     */
    public function getSnapshot($object);

    /**
     * Make new snapshots for all registered objects
     * 
     * @api
     */
    public function makeNewSnapshots();

    /**
     * @param mixed $object
     * @return bool
     * 
     * @api
     */
    public function isRemoved($object);

    /**
     * Marks object as "removed"
     *
     * @param mixed $object
     * 
     * @api
     */
    public function remove($object);

    /**
     * Cleans all objects marked as removed
     * 
     * @api
     */
    public function cleanRemoved();

    /**
     * @return array
     * 
     * @api
     */
    public function all();

    /**
     * Restore object states from their snapshots.
     * 
     * @api
     */
    public function reset();
}
