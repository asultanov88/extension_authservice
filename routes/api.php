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
 * Add new user profile for registered client.
 */
Route::middleware('clientUserAuth')->post('/user_profile', [RegisterClientController::class, 'addUserProfile']);

/**
 * Confirm user registration.
 */
Route::post('/confirm_user', [GetConfigController::class, 'confirmUserRegistrationCode']);

/**
 * Gets config object based on registration key.
 * middleware('client') - ensures user has a valied registration key.
 * 'client' middleware can be modified at: app\Http\Middleware\ValidateRegKey.php
 */
Route::post('/get_config', [GetConfigController::class, 'getConfig']);

/**
 * Gets user profile list by search string.
 */
Route::middleware('clientUserAuth')->get('/user-profiles', [RegisterClientController::class, 'getUserProfiles']);
/**
 * Updates user profile.
 */
Route::middleware('clientUserAuth')->patch('/user-profile', [RegisterClientController::class, 'patchUserProfile']);
