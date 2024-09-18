<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::post('/vendor/register', [VendorController::class, 'store'])
    ->middleware('auth:sanctum');
