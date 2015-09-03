<?php

namespace Isolate\UnitOfWork;

/**
 * @api
 */
final class EntityStates
{
    /**
     * New created entity that was not persisted yet
     */
    const NEW_ENTITY = 0;

    /**
     * Persisted entity
     */
    const PERSISTED_ENTITY = 1;

    /**
     * Persisted object that was saved to data source in past but it was
     * modified after registration in unit of work.
     */
    const EDITED_ENTITY = 2;

    /**
     * Persisted entity that should be deleted
     */
    const REMOVED_ENTITY = 3;
}
