<?php

namespace Isolate\UnitOfWork\Tests\Double;

class EntityFakeChild
{
    /**
     * @var null
     */
    private $id;

    /**
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public static function getClassName()
    {
        return __CLASS__;
    }
}
