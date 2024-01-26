<?php

use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Event\SingleEventController;
use App\Http\Controllers\File\FileController;
use App\Http\Controllers\User\UserController;
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
    });
});

// Public EventController
Route::prefix('/singleEvents')->group(function () {
    Route::controller(SingleEventController::class)->group(function () {
        Route::get('/list', 'getSingleEvents');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // UserController
    Route::prefix('/user')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('/list', 'listUser');
            Route::get('/', 'getUser');
        });
    });

    // EventController
    Route::prefix('/singleEvents')->group(function () {
        Route::controller(SingleEventController::class)->group(function () {
            Route::post('/add', 'addSingleEvent');
        });
    });

    // FileController
    Route::prefix('/files')->group(function () {
        Route::controller(FileController::class)->group(function () {
            Route::post('', 'uploadImage');
        });
    });
});
