<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MercadoLibreController;
use App\Http\Controllers\API\MetricsController;
use App\Http\Controllers\API\OpinionController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\RandomController;
use App\Http\Controllers\API\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile/{user}', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/search-products', [MercadoLibreController::class, 'searchProducts']);
    Route::get('/products/get-product', [MercadoLibreController::class, 'getProductInformation']);
    Route::get('/users/{user}/favourites', [UserController::class, 'favourites']);
    Route::get('/users/{user}/purchases', [UserController::class, 'purchases']);
    Route::get('/users', [UserController::class, 'index'])->middleware(AdminMiddleware::class);
    Route::post('/purchase', [ProductController::class, 'purchase']);
    Route::put('/products/favourite', [ProductController::class, 'favourite']);
    Route::get('/metrics', [MetricsController::class, 'getMetrics'])->middleware(AdminMiddleware::class);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/answer', [RandomController::class, 'responseToLifeTheUniverseAndEverything']);

    Route::apiResource('opinions', OpinionController::class);
});

