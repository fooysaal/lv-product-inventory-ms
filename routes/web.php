<?php

use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Auth routes - all handled by Vue Router
Route::view('/login', 'auth.login')->name('login');
Route::view('/dashboard', 'auth.login')->middleware('auth:sanctum');

// Catch-all route for Vue Router (SPA)
Route::get('/{any}', function () {
    return view('auth.login');
})->where('any', '.*');
