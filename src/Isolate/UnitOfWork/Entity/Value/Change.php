<?php

namespace Isolate\UnitOfWork\Entity\Value;

use Isolate\UnitOfWork\Entity\Definition\Property;

/**
 * @api
 */
interface Change
{
    /**
     * @return mixed
     * 
     * @api
     */
    public function getOriginValue();

    /**
     * @return mixed
     * 
     * @api
     */
    public function getNewValue();

    /**
     * @return Property
     * 
     * @api
     */
    public function getProperty();

    /**
     * @param string $propertyName
     * @return mixed
     * 
     * @api
     */
    public function isFor($propertyName);
}
