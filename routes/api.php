<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post("register", "register");
    Route::post("login", "login");
    Route::get('verify-email', 'verifyEmail');
    Route::post('forgot-password', 'sendResetLinkEmail');
    Route::post('reset-password', 'resetPassword');

    Route::get('profile', 'profile')->middleware('auth:sanctum');
    Route::get('logout', 'logout')->middleware('auth:sanctum');
});

Route::controller(AdminController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('allusers', 'getAllUsers');
    Route::get('user/{id}', 'getUserById');
    Route::patch('user/{id}/suspend', 'suspendUser');
    Route::delete('user/{id}', 'deleteUser');
});
;
