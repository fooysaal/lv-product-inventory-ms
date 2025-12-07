<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
});

// Protected Authentication Routes
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
    Route::get('/profile', [AuthController::class, 'profile'])->name('api.auth.profile');
});

// Legacy route - kept for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
