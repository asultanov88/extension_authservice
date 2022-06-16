<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterClientController;
use App\Http\Controllers\GetConfigController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register_admin', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

/**
 * Registers a new client.
 */
Route::middleware('auth:api')->post('/register_client', [RegisterClientController::class, 'registerClient']);

/**
 * Gets config object based on registration key.
 * middleware('client') - ensures user has a valied registration key.
 * 'client' middleware can be modified at: app\Http\Middleware\ValidateRegKey.php
 */
Route::middleware('client')->post('/get_config', [GetConfigController::class, 'getConfig']);
