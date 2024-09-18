<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::post('/vendor/register', [VendorController::class, 'store'])
    ->middleware('auth:sanctum');
Route::get('/vendor/list', [VendorController::class, 'index']);
Route::get('/vendor/search/{name}', [VendorController::class, 'search']);
Route::get('/vendor/{id}', [VendorController::class, 'show']);
Route::delete('/vendor/{id}', [VendorController::class, 'destroy'])
    ->middleware('auth:sanctum');

Route::get('/category/list', [CategoryController::class, 'index']);
Route::get('/category/products/{category}', [CategoryController::class, 'show'])
    ->missing(function () {
        return response()->json([
            'message' => 'Category does not exist',
        ]);
    });
Route::apiResource('products', ProductController::class);
Route::post('products', [ProductController::class, 'store'])->middleware('auth:api');
Route::post('products/{id}', [ProductController::class, 'update'])->middleware('auth:api');
