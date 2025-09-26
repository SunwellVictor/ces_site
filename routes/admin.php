<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\FileController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Admin panel routes protected by authentication and admin role middleware.
| These routes provide CRUD functionality for managing the application.
|
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::resource('users', UserController::class);
    
    // Product Management
    Route::resource('products', ProductController::class);
    
    // Order Management
    Route::resource('orders', OrderController::class);
    
    // Blog/Post Management
    Route::resource('posts', PostController::class);
    
    // File Management
    Route::resource('files', FileController::class);
    Route::post('files/{file}/toggle-status', [FileController::class, 'toggleStatus'])->name('files.toggle-status');
    
});