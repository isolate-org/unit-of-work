<?php

namespace Isolate\UnitOfWork;

final class Events
{
    const PRE_COMMIT = 'isolate.unit_of_work.pre_commit';

    const POST_COMMIT = 'isolate.unit_of_work.post_commit';

    const PRE_REGISTER_OBJECT = 'isolate.unit_of_work.pre_register_object';

    const PRE_GET_OBJECT_STATE = 'isolate.unit_of_work.pre_get_object_state';

    const PRE_REMOVE_OBJECT = 'isolate.unit_of_work.pre_remove_object';
}
