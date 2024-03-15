<?php

use App\Models\Permissions;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Temporary pubic dev control
Route::get('/migrate', function () {
    Artisan::call('migrate');
});

Route::get('/seed', function() {
    Artisan::call('db:seed');
});

Route::get('/create/{username}/{email}/{password}', function (string $username, string $email, string $password) {
    $user = new User([
        'name' => $username,
        'email' => $email,
        'password' => Hash::make($password)
    ]);
    $user->givePermissionTo(Permissions::cases());
    $user->save();
});

Route::get('/linkstorage', function () {
    Artisan::call('storage:link');
});

Route::get('/{reactRoute?}', function() {
    return File::get(public_path().'/react.html');
})->where('reactRoute', '^(?!\/storage\/uploads).+');
