<?php

namespace Isolate\UnitOfWork\Entity\Property;

use Isolate\UnitOfWork\Entity\Definition\Property;

/**
 * @api
 */
interface ValueComparer
{
    /**
     * @param Property $property
     * @param $firstObject
     * @param $secondObject
     * @return mixed
     * 
     * @api
     */
    public function hasDifferentValue(Property $property, $firstObject, $secondObject);
}