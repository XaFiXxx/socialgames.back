<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* ------------------ ROUTES FOR LOGIN / REGISTER ------------------ */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



/* ------------------- ROUTES FOR GAMES ------------------- */

Route::get('/games/index', [GameController::class, 'index']);

/* ------------------- ROUTES FOR USERS ------------------- */

Route::get('/users/{id}/profile', [UserController::class, 'userProfile']);


/* ------------------- ROUTES FOR DASHBOARD ------------------- */

Route::post('/dashboard/login', [AuthController::class, 'dashboardLogin']);