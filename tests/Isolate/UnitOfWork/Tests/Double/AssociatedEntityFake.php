<?php

namespace Isolate\UnitOfWork\Tests\Double;

class AssociatedEntityFake
{
    /**
     * @var null
     */
    private $id;

    /**
     * @var array|EntityFakeChild[]
     */
    private $children;

    /**
     * @var AssociatedEntityFake
     */
    private $parent;
    /**
     * @var null
     */
    private $name;

    /**
     * @param null $id
     * @param null $name
     * @param AssociatedEntityFake $parent
     * @param array|EntityFakeChild[] $children
     */
    public function __construct($id = null, $name = null, AssociatedEntityFake $parent = null, $children = [])
    {
        $this->id = $id;
        $this->children = [];
        $this->parent = $parent;
        $this->children = $children;
        $this->name = $name;
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

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param AssociatedEntityFake $child
     */
    public function addChild(AssociatedEntityFake $child)
    {
        $this->children[] = $child;
    }

    /**
     * @param $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function removeParent()
    {
        $this->parent = null;
    }

    /**
     * @return AssociatedEntityFake
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return array|EntityFakeChild[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    public static function getClassName()
    {
        return __CLASS__;
    }
}
