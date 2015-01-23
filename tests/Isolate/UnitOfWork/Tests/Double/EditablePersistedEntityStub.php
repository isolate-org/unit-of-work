<?php

namespace Isolate\UnitOfWork\Tests\Double;

class EditablePersistedEntityStub
{
    protected $name;

    public function __construct()
    {
        $this->name = "default";
    }

    public function changeName($name)
    {
        $this->name = $name;
    }

    public function getId()
    {
        return 1;
    }
}
