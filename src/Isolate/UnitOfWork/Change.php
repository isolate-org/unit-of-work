<?php

namespace Isolate\UnitOfWork;

final class Change
{
    /**
     * @var mixed
     */
    private $originValue;

    /**
     * @var mixed
     */
    private $newValue;

    /**
     * @var string
     */
    private $propertyName;

    /**
     * @param $originValue
     * @param $newValue
     * @param string $propertyName
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

    /**
     * @param $propertyName
     * @return bool
     */
    public function isFor($propertyName)
    {
        return $this->propertyName === $propertyName;
    }
}
