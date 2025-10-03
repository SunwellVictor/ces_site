<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;

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
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
    Route::post('users/bulk-update', [UserController::class, 'bulkUpdate'])->name('users.bulk-update');
    
    // Role Management
    Route::resource('roles', RoleController::class);
    Route::post('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
    
    // Product Management
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/attach-files', [ProductController::class, 'attachFiles'])->name('products.attach-files');
    Route::delete('products/{product}/files/{file}', [ProductController::class, 'detachFile'])->name('products.detach-file');
    
    // File Management
    Route::resource('files', FileController::class);
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::post('files/{file}/replace', [FileController::class, 'replace'])->name('files.replace');
    
    // Order Management
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
    Route::post('orders/{order}/mark-completed', [OrderController::class, 'markCompleted'])->name('orders.mark-completed');
    Route::post('orders/{order}/mark-failed', [OrderController::class, 'markFailed'])->name('orders.mark-failed');
    Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
    
    // Blog/Post Management
    Route::resource('posts', PostController::class);
    Route::post('posts/{post}/duplicate', [PostController::class, 'duplicate'])->name('posts.duplicate');
    Route::post('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::post('posts/{post}/unpublish', [PostController::class, 'unpublish'])->name('posts.unpublish');
    Route::post('posts/{post}/toggle-featured', [PostController::class, 'toggleFeatured'])->name('posts.toggle-featured');
    
    // Category Management
    Route::resource('categories', CategoryController::class);
    Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::get('categories/{category}/posts', [CategoryController::class, 'posts'])->name('categories.posts');
    Route::post('categories/bulk-update', [CategoryController::class, 'bulkUpdate'])->name('categories.bulk-update');
    
});