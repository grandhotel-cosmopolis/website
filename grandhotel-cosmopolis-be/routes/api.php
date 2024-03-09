<?php

use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Event\EventLocationController;
use App\Http\Controllers\Event\RecurringEventController;
use App\Http\Controllers\Event\SingleEventController;
use App\Http\Controllers\File\FileController;
use App\Http\Controllers\User\UserController;
use App\Models\Permissions;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// LoginController
Route::prefix('/login')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::post('', 'authenticate');
        Route::post('rememberMe', 'rememberMe');
    });
});

// Public EventController
Route::prefix('/singleEvent')->group(function () {
    Route::controller(SingleEventController::class)->group(function () {
        Route::get('/list', 'getSingleEvents');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // UserController
    Route::prefix('/user')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::middleware('permission:' . Permissions::VIEW_USERS->value)->get('/list', 'list');
            Route::get('/', 'getUser');
        });
    });

    // EventController
    Route::prefix('/singleEvent')->group(function () {
        Route::controller(SingleEventController::class)->group(function () {
            Route::middleware('permission:' . Permissions::CREATE_EVENT->value)->post('', 'create');
            Route::middleware('permission:' . Permissions::DELETE_EVENT->value)->delete('/{eventGuid}', 'delete');
            Route::middleware('permission:' . Permissions::EDIT_EVENT->value)->post('/{eventGuid}/update', 'update');
            Route::middleware('permission:' . Permissions::PUBLISH_EVENT->value)->post('/{eventGuid}/publish', 'publish');
            Route::middleware('permission:' . Permissions::UNPUBLISH_EVENT->value)->post('/{eventGuid}/unpublish', 'unpublish');
            Route::middleware('permission:' . Permissions::VIEW_EVENTS->value)->get('/listAll', 'listAll');
            Route::middleware('permission:' . Permissions::EDIT_EVENT->value)->post('/{eventGuid}/exception', 'createOrUpdateException');
        });
    });

    // RecurringEventController
    Route::prefix('/recurringEvent')->group(function () {
        Route::controller(RecurringEventController::class)->group(function () {
            Route::middleware('permission:' . Permissions::CREATE_EVENT->value)->post('/', 'create');
            Route::middleware('permission:' . Permissions::EDIT_EVENT->value)->post('/{eventGuid}/update', 'update');
            Route::middleware('permission:' . Permissions::DELETE_EVENT->value)->delete('/{eventGuid}', 'delete');
            Route::middleware('permission:' . Permissions::PUBLISH_EVENT->value)->post('/{eventGuid}/publish', 'publish');
            Route::middleware('permission:' . Permissions::UNPUBLISH_EVENT->value)->post('/{eventGuid}/unpublish', 'unpublish');
            Route::middleware('permission:' . Permissions::VIEW_EVENTS->value)->get('/listAll', 'listAll');
            Route::middleware('permission:' . Permissions::VIEW_EVENTS->value)->get('/{eventGuid}/listSingleEvents', 'listAllSingleEventsByRecurringEventId');
        });
    });

    // FileController
    Route::prefix('/file')->group(function () {
        Route::controller(FileController::class)->group(function () {
            Route::middleware('permission:' . Permissions::CREATE_EVENT->value)->post('/upload', 'uploadImage');
        });
    });

    // EventLocationController
    Route::prefix('/eventLocation')->group(function () {
        Route::controller(EventLocationController::class)->group(function () {
            Route::middleware('permission:' . Permissions::CREATE_EVENT->value)->post('', 'create');
            Route::middleware('permission:' . Permissions::DELETE_EVENT->value)->delete('/{eventLocationGuid}', 'delete');
            Route::middleware('permission:' . Permissions::EDIT_EVENT->value)->post('/{eventLocationGuid}/update', 'update');
            Route::middleware('permission:' . Permissions::CREATE_EVENT->value)->get('/list', 'list');
        });
    });
});
