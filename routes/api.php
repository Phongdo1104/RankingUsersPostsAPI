<?php

use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\UserController;
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

Route::post('/signup', [UserController::class, 'signup']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/ranks', [UserController::class, 'ranks']);

Route::middleware('auth:api')->group(function () {
    Route::group(['prefix' => 'user'], function() {
        Route::get('/', [UserController::class, 'get']);
        Route::put('/edited', [UserController::class, 'update']);
        Route::post('/avatar/edited', [UserController::class, 'updateAvatar']);
        Route::post('/logout', [UserController::class, 'logout']);
        
        Route::get('/rank', [UserController::class, 'rank']);
    });
    
    Route::group(['prefix' => 'post'], function() {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{id}', [PostController::class, 'get']);
        Route::put('/{id}/edited', [PostController::class, 'update']);
        Route::post('/add', [PostController::class, 'store']);
        Route::delete('/{id}/delete', [PostController::class, 'delete']);
    });

    Route::group(['prefix' => 'comment'], function() {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{id}', [PostController::class, 'get']);
        Route::put('/{id}/edited', [PostController::class, 'update']);
        Route::post('/add', [PostController::class, 'store']);
        Route::delete('/{id}/delete', [PostController::class, 'delete']);
    });
});
