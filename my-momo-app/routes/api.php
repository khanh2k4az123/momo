<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\OrderController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/packages', [PackageController::class, 'index']);

    Route::post('/create-order', [OrderController::class, 'createOrder']);
});
Route::post('/simulate-ipn', [OrderController::class, 'simulateIpn']);
// Callback routes
Route::get('/payment/return', [OrderController::class, 'paymentReturn']);
Route::post('/payment/notify', [OrderController::class, 'paymentNotify']);
