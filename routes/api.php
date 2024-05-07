<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PlateformController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\GroupController;

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

    // ------------------- ROUTES FOR HOME ------------------- //
    Route::get('/home', [HomeController::class, 'home']);

    // ------------------- ROUTES FOR USERS ------------------- //
    Route::get('/users/{id}/profile', [UserController::class, 'userProfile']);
    Route::get('/user/platforms', [UserController::class, 'userPlatforms']);
    Route::post('/user/update/platforms', [UserController::class, 'updatePlatforms']);
    Route::post('/user/follow/{id}', [UserController::class, 'toggleFollowUser']);

    // ------------------- ROUTES FOR GAMES ------------------- //
    Route::get('/games/index', [GameController::class, 'index']);
    Route::post('/games/{game}/follow', [GameController::class, 'toggleFollow']);

    // ------------------- ROUTES FOR GROUPS ------------------- //
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/group/{id}', [GroupController::class, 'show']);

    // ------------------- ROUTES FOR POSTS ------------------- //
    Route::post('/create/post', [PostController::class, 'createUserPost']);

    // ------------------- ROUTES FOR PLATEFORMS ------------------- //
    Route::get('/platforms', [PlateformController::class, 'index']);

    // ------------------- ROUTES FOR SEARCH ------------------- //
    Route::get('/search', [SearchController::class, 'searchAll']);
    Route::get('/search/suggestions', [SearchController::class, 'getSuggestions']);
    Route::get('/profil/{id}/{username}', [UserController::class, 'showUserById']);
    Route::get('/game/{id}/{name}', [GameController::class, 'show']);
});


