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

// Protéger toutes les routes API nécessitant une authentification
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/games/index', [GameController::class, 'index']);
    Route::get('/users/{id}/profile', [UserController::class, 'userProfile']);
    // Autres routes protégées
});


/* ------------------ ROUTES FOR LOGIN / REGISTER ------------------ */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/* ------------------- ROUTES FOR DASHBOARD ------------------- */

Route::post('/dashboard/login', [AuthController::class, 'dashboardLogin']);