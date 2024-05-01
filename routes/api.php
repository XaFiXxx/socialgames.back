<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PlateformController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/* ------------------ ROUTES FOR LOGIN / REGISTER ------------------ */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/* ------------------- ROUTES FOR LOGIN DASHBOARD ------------------- */

Route::post('/dashboard/login', [AuthController::class, 'dashboardLogin']);

// Routes API nécessitant une authentification
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
   

    // ------------------- ROUTES FOR USERS ------------------- //
    Route::get('/users/{id}/profile', [UserController::class, 'userProfile']);
    Route::get('/user/platforms', [UserController::class, 'userPlatforms']);
    Route::post('/user/update/platforms', [UserController::class, 'updatePlatforms']);

    // ------------------- ROUTES FOR GAMES ------------------- //
    Route::get('/games/index', [GameController::class, 'index']);
    Route::post('/games/{game}/follow', [GameController::class, 'toggleFollow']);


    // ------------------- ROUTES FOR POSTS ------------------- //
    Route::post('/create/post', [PostController::class, 'createUserPost']);

    // ------------------- ROUTES FOR PLATEFORMS ------------------- //
    Route::get('/platforms', [PlateformController::class, 'index']);
});


