<?php

use App\Http\Controllers\auth_controller\LoginController;
use App\Http\Controllers\auth_controller\RegisterController;
use Illuminate\Http\Request;
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

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);



Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [LoginController::class, 'logout']);
});
