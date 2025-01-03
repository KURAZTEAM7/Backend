<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::post('/vendor/register', [VendorController::class, 'store'])
    ->middleware('auth:sanctum');
Route::post('/vendor/register/bylicense', [VendorController::class, 'storePopulated'])
    ->middleware('auth:sanctum');
Route::get('/vendor/list', [VendorController::class, 'index']);
Route::get('/vendor/search/{name}', [VendorController::class, 'search']);
Route::get('/vendor/products', [VendorController::class, 'products'])
    ->middleware('auth:sanctum');
Route::get('/vendor/orders', [VendorController::class, 'orders'])
    ->middleware('auth:sanctum');
Route::get('/vendor/{id}', [VendorController::class, 'show']);
Route::delete('/vendor/', [VendorController::class, 'destroy'])
    ->middleware('auth:sanctum');

Route::get('/category/list', [CategoryController::class, 'index']);
Route::get('/category/products/{category}', [CategoryController::class, 'show'])
    ->missing(function () {
        return response()->json([
            'message' => 'Category does not exist',
        ]);
    });

Route::get('/product/search', [ProductController::class, 'search']);
Route::get('/product/list', [ProductController::class, 'index']);
Route::post('/product/add', [ProductController::class, 'store'])
    ->middleware('auth:sanctum');
Route::get('/product/{id}', [ProductController::class, 'show']);
Route::delete('/product/{id}', [ProductController::class, 'destroy'])
    ->middleware('auth:sanctum');

Route::get('/user/orders', [UserController::class, 'orders'])
    ->middleware('auth:sanctum');
Route::get('/orders/add', [OrderController::class, 'store'])
    ->middleware('auth:sanctum');
