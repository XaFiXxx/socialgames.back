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
use App\Http\Controllers\GameReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\FriendController;

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
Route::middleware('auth:sanctum', 'check.token.expiration')->group(function () {

    Route::post('/broadcasting/auth', function () {
        return Broadcast::auth(request());
    });

    // Route::get('/user', [AuthController::class, 'user']);
    // ------------------- ROUTES FOR HOME ------------------- //
    Route::get('/home', [HomeController::class, 'home']);

    // ------------------- ROUTES FOR USERS ------------------- //
    Route::get('/users/{id}/profile', [UserController::class, 'userProfile']);
    Route::get('/user/platforms', [UserController::class, 'userPlatforms']);
    Route::post('/user/update/platforms', [UserController::class, 'updatePlatforms']);
    Route::post('/user/follow/{id}', [UserController::class, 'toggleFollowUser']);
    Route::get('/user/groups', [UserController::class, 'userGroups']);
    Route::post('/user/update/profil_img', [UserController::class, 'updateProfilImg']);
    Route::post('/user/update/cover_img', [UserController::class, 'updateCoverImg']);
    Route::post('/users/update-profile', [UserController::class, 'updateProfile']);

    // ------------------- ROUTES FOR GAMES ------------------- //
    Route::get('/games/index', [GameController::class, 'index']);
    Route::post('/games/{game}/follow', [GameController::class, 'toggleFollow']);
    Route::post('/games/{game}/rate', [GameReviewController::class, 'rateGame']);
    Route::post('/games/{game}/rate/delete', [GameReviewController::class, 'rateGameDelete']);
    Route::post('/games/{game}/rate/update', [GameReviewController::class, 'rateGameUpdate']);

    // ------------------- ROUTES FOR GROUPS ------------------- //
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/group/{id}', [GroupController::class, 'show']);
    Route::post('/group/{id}/follow', [GroupController::class, 'followGroup']);
    Route::post('/group/create', [GroupController::class, 'store']);
    Route::post('/group/{id}/delete', [GroupController::class, 'deleteGroup']);

    // ------------------- ROUTES FOR POSTS ------------------- //
    Route::post('/create/post', [PostController::class, 'createUserPost']);
    Route::post('/create/groupPost', [PostController::class, 'createGroupPost']);
    Route::post('/post/{id}/like', [PostController::class, 'likePost']);

    // ------------------- ROUTES FOR COMMENTS ------------------- //
    Route::post('/post/{id}/comment', [CommentController::class, 'createComment']);

    // ------------------- ROUTES FOR PLATEFORMS ------------------- //
    Route::get('/platforms', [PlateformController::class, 'index']);

    // ------------------- ROUTES FOR SEARCH ------------------- //
    Route::get('/search', [SearchController::class, 'searchAll']);
    Route::get('/search/suggestions', [SearchController::class, 'getSuggestions']);
    Route::get('/profil/{id}/{username}', [UserController::class, 'showUserById']);
    Route::get('/game/{id}/{name}', [GameController::class, 'show']);

    // ------------------- ROUTES FOR FRIENDS ------------------- //
    Route::post('/user/add-friend', [FriendController::class, 'addFriend']);
    Route::post('/respond-friend-request', [FriendController::class, 'respondToFriendRequest']);
    Route::get('/friends', [FriendController::class, 'listFriends']);
    Route::get('/friend-requests', [FriendController::class, 'listFriendRequests']);

    // ------------------- ROUTES FOR LOGOUT ------------------- //
    Route::post('/logout', [AuthController::class, 'logout']);

    // ------------------- ROUTES FOR DASHBOARD ------------------- //
    Route::middleware('is_admin')->group(function () {

        // ------------------- ROUTES FOR USERS ------------------- //
        Route::get('/dashboard/users', [UserController::class, 'index']);
        Route::post('/dashboard/user/is_admin', [UserController::class, 'is_admin']);
        Route::post('/dashboard/users/create', [UserController::class, 'store']);
        Route::post('/dashboard/users/{userId}/update', [UserController::class, 'update']);
        Route::delete('/dashboard/users/{userId}/delete', [UserController::class, 'deleteUser']);

        // ------------------- ROUTES FOR GAMES ------------------- //
        Route::get('/dashboard/games', [GameController::class, 'index']);
        Route::post('/dashboard/games/create', [GameController::class, 'store']);
        Route::post('/dashboard/games/{id}/update', [GameController::class, 'update']);
        Route::delete('/dashboard/games/{id}/delete', [GameController::class, 'delete']);

        // ------------------- ROUTES FOR PLATEFORMS ------------------- //
        Route::get('/dashboard/platforms', [PlateformController::class, 'index']);
        Route::post('/dashboard/platforms/create', [PlateformController::class, 'store']);
        Route::post('/dashboard/platforms/{id}/update', [PlateformController::class, 'update']);
        Route::delete('/dashboard/platforms/{id}/delete', [PlateformController::class, 'delete']);

        // ------------------- ROUTES FOR GENRE ------------------- //
        Route::get('/genres', [GenreController::class, 'index']);
        Route::get('/dashboard/genres', [GenreController::class, 'index']);
        Route::post('/dashboard/genres/create', [GenreController::class, 'store']);
        Route::post('/dashboard/genres/{id}/update', [GenreController::class, 'update']);
        Route::delete('/dashboard/genres/{id}/delete', [GenreController::class, 'delete']);

        // ------------------- ROUTES FOR GROUPS ------------------- //
        Route::post('/dashboard/groups/create', [GroupController::class, 'storeDashboard']);
        Route::post('/dashboard/groups/{id}/update', [GroupController::class, 'updateDashboard']);
        Route::delete('/dashboard/groups/{id}/delete', [GroupController::class, 'deleteDashboard']);

        // ------------------- ROUTES FOR POSTS ------------------- //
        Route::get('/dashboard/posts', [PostController::class, 'index']);
        Route::delete('/dashboard/posts/{id}/delete', [PostController::class, 'deleteDashboard']);
        Route::post('/dashboard/posts/{id}/update', [PostController::class, 'updateDashboard']);

    });
});
