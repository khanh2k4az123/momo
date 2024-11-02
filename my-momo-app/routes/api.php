<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VipController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackageController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/payment/simulate-ipn', [OrderController::class, 'simulateIpn']);
Route::get('/payment/return', [OrderController::class, 'paymentReturn']);
Route::post('/payment/notify', [OrderController::class, '']);
Route::middleware('auth:api')->get('/user/vip-status', [UserController::class, 'getVipStatus']);



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'getUser']);
    Route::get('/packages', [PackageController::class, 'index']);
    Route::post('/create-order', [OrderController::class, 'createOrder']);
    Route::get('/user/vip-status', [UserController::class, 'getVipStatus']);
});

// VIP-Protected routes
Route::middleware(['auth:sanctum', 'vip'])->group(function () {
    Route::get('/vip-content', [VipController::class, 'index']);
});
