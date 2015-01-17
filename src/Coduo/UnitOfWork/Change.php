<?php

namespace Coduo\UnitOfWork;

final class Change
{
    private $originValue;
    private $newValue;
    private $propertyName;

    /**
     * @param $originValue
     * @param $newValue
     * @param $propertyName
     */
    public function __construct($originValue, $newValue, $propertyName)
    {
        $this->originValue = $originValue;
        $this->newValue = $newValue;
        $this->propertyName = $propertyName;
    }

    /**
     * @return mixed
     */
    public function getOriginValue()
    {
        return $this->originValue;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @return mixed
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function isFor($propertyName)
    {
        return $this->propertyName === $propertyName;
    }
}
