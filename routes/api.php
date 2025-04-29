<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProductController;

Route::post('/login', [LoginController::class, 'login']);
Route::middleware('token')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);    
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'delete']);
    Route::get('/total-stock-value/{id}', [ProductController::class, 'totalStockValue']);
    Route::get('/highest-price', [ProductController::class, 'highestPrice']);
    Route::get('/tags/most-used', [ProductController::class, 'mostUsedTag']);
});


