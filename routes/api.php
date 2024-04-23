<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', [UserController::class, 'logout']);
    Route::get('me', [UserController::class, 'me']);
});
