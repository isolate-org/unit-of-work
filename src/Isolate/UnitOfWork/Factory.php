<?php

namespace Isolate\UnitOfWork;

interface Factory
{
    /**
     * @return UnitOfWork
     * 
     * @api
     */
    public function create();
}
