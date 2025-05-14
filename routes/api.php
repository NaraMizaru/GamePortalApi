<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GameController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('/signin', 'signin');
            Route::post('/signup', 'signup');
            Route::post('/signout', 'signout')->middleware('auth:sanctum');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('/admins', 'getAdmins');

            Route::post('/users', 'addUser');
            Route::get('/users', 'getUsers');
            Route::put('/users/{id}', 'editUsers');
            Route::delete('/users/{id}', 'deleteUsers');
        });

        Route::controller(GameController::class)->group(function () {
            Route::get('/games', 'getGames');
        });
    });
});
