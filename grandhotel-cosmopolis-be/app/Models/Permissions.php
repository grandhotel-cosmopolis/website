<?php

namespace App\Models;
use OpenApi\Attributes as OA;

#[OA\Schema]
enum Permissions: string
{
    case CREATE_EVENT = 'CREATE_EVENT';
    case PUBLISH_EVENT = 'PUBLISH_EVENT';
    case UNPUBLISH_EVENT = 'UNPUBLISH_EVENT';
    case EDIT_EVENT = 'EDIT_EVENT';
    case DELETE_EVENT = 'DELETE_EVENT';
    case VIEW_EVENTS = 'VIEW_EVENTS';

    case CREATE_USER = 'CREATE_USER';
    case UPDATE_USER = 'UPDATE_USER';
    case DELETE_USER = 'DELETE_USER';
    case VIEW_USERS = 'VIEW_USERS';
}
