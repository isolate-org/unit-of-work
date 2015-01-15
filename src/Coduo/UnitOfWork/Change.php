<?php

namespace Coduo\UnitOfWork;

final class Change
{
    private $originValue;
    private $newValue;
    private $propertyPath;

    /**
     * @param $originValue
     * @param $newValue
     * @param $propertyPath
     */
    public function __construct($originValue, $newValue, $propertyPath)
    {
        $this->originValue = $originValue;
        $this->newValue = $newValue;
        $this->propertyPath = $propertyPath;
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
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    public function hasSame($propertyPath)
    {
        return $this->propertyPath === $propertyPath;
    }
}
