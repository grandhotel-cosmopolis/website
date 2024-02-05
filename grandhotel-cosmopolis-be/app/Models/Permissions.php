<?php

namespace App\Models;
use OpenApi\Attributes as OA;

#[OA\Schema]
enum Permissions: string
{
    case CREATE_EVENT = 'create_event';
    case PUBLISH_EVENT = 'publish_event';
    case UNPUBLISH_EVENT = 'unpublish_event';
    case EDIT_EVENT = 'edit_event';
    case DELETE_EVENT = 'delete_event';
}
