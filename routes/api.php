<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserEmailController;
use App\Http\Controllers\Api\UserMailController;
use Illuminate\Support\Facades\Route;

Route::apiResource('users', UserController::class);

Route::post('/users/{user}/emails', [UserEmailController::class, 'store'])->name('users.emails.store');
Route::patch('/users/{user}/emails/{email}', [UserEmailController::class, 'update'])->name('users.emails.update');
Route::delete('/users/{user}/emails/{email}', [UserEmailController::class, 'destroy'])->name('users.emails.destroy');

Route::post('/users/{user}/send-welcome', [UserMailController::class, 'sendWelcome']);
