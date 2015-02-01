<?php

namespace Isolate\UnitOfWork\Entity\Value;

use Isolate\UnitOfWork\Entity\Definition\Property;

interface Change
{
    /**
     * @return mixed
     */
    public function getOriginValue();

    /**
     * @return mixed
     */
    public function getNewValue();

    /**
     * @return Property
     */
    public function getProperty();

    /**
     * @param string $propertyName
     * @return mixed
     */
    public function isFor($propertyName);
}
