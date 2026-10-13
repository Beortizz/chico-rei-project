<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
    Route::get('product-logs', [ProductLogController::class, 'index']);
});
