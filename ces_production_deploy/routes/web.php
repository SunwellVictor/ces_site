<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Webhook\StripeController;
use Illuminate\Support\Facades\Route;

// Webhook Routes (no auth/CSRF required)
Route::post('/webhooks/stripe', [StripeController::class, 'handle'])->name('webhooks.stripe');

Route::get('/', [HomeController::class, 'index'])->name('home');

// Test route for modal debugging
Route::get('/test-modal', function () {
    return view('test-modal');
})->name('test.modal');

// Public Blog Routes
Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category:slug}', [PostController::class, 'category'])->name('blog.category');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');

// Public Product Routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Cart Routes (session-based, no auth required)
Route::get('/cart', [CartController::class, 'show'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{product}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes (require auth)
Route::middleware('auth')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'createSession'])->name('checkout.create');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});

// Account Routes (require auth and email verification)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/account', [AccountController::class, 'dashboard'])->name('account.dashboard');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{order}', [AccountController::class, 'showOrder'])->name('account.orders.show');
    Route::get('/account/downloads', [DownloadController::class, 'index'])->name('account.downloads');
    Route::get('/account/profile/edit', [AccountController::class, 'editProfile'])->name('account.profile.edit');
    Route::patch('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
});

// Download Routes (require auth)
Route::middleware('auth')->group(function () {
    Route::get('/downloads', [DownloadController::class, 'index'])->name('downloads.index');
    Route::post('/downloads/{grant}/token', [DownloadController::class, 'issueToken'])
        ->name('downloads.token');
    Route::get('/downloads/stats', [DownloadController::class, 'getDownloadStats'])->name('downloads.stats');
    Route::get('/downloads/grants/{grant}/status', [DownloadController::class, 'checkGrantStatus'])->name('downloads.grant.status');
});

// Public download consumption (no auth required - uses token)
Route::get('/download/{token}', [DownloadController::class, 'consumeToken'])->name('downloads.consume');

// SEO Routes
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-posts.xml', [SitemapController::class, 'posts'])->name('sitemap.posts');
Route::get('/sitemap-products.xml', [SitemapController::class, 'products'])->name('sitemap.products');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
