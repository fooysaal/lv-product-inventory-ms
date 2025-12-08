<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
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

// Dashboard Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // User Type Management
    Route::apiResource('user-types', UserTypeController::class)->middleware('admin');
    Route::patch('/user-types/{userType}/toggle-status', [UserTypeController::class, 'toggleStatus'])->name('user-types.toggle-status')->middleware('admin');

    // User Management
    Route::get('/users/user-types', [UserController::class, 'getUserTypes'])->name('users.user-types')->middleware('admin');
    Route::apiResource('users', UserController::class)->middleware('admin');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status')->middleware('admin');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password')->middleware('admin');

    // Category Management
    Route::get('/categories/all', [CategoryController::class, 'getAllCategories'])->name('categories.all');
    Route::apiResource('categories', CategoryController::class);
    Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Unit Management
    Route::get('/units/all', [UnitController::class, 'getAllUnits'])->name('units.all');
    Route::apiResource('units', UnitController::class);
    Route::patch('/units/{unit}/toggle-status', [UnitController::class, 'toggleStatus'])->name('units.toggle-status');

    // Product Management
    Route::get('/products/form-data', [ProductController::class, 'getFormData'])->name('products.form-data');
    Route::apiResource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
});

// Legacy route - kept for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
