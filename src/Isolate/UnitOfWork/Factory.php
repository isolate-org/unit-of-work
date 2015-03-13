<?php

namespace Isolate\UnitOfWork;

interface Factory
{
    /**
     * @return UnitOfWork
     */
    public function create();
}
