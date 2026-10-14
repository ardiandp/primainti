<?php

use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\GalleryCategoryController;
use App\Http\Controllers\Admin\GalleryItemController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PageShowController;
use App\Http\Controllers\ProductCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.attempt');

    // Reset password (broker bawaan Laravel; email via MAIL_MAILER di .env)
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
        ->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware(['auth', 'active'])
    ->name('logout');

// Area admin (route lain menyusul di task berikutnya)
Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');

    Route::resource('users', UserController::class)
        ->except(['show', 'destroy'])
        ->parameters(['users' => 'user']);

    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::post('users/{user}/reset-access', [UserController::class, 'resetAccess'])->name('users.reset-access');

    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->parameters(['roles' => 'role']);

    // Media Library
    Route::get('media', [MediaLibraryController::class, 'index'])->name('media.index');
    Route::post('media', [MediaLibraryController::class, 'store'])->name('media.store');
    Route::get('media/{media}/edit', [MediaLibraryController::class, 'edit'])->name('media.edit');
    Route::put('media/{media}', [MediaLibraryController::class, 'update'])->name('media.update');
    Route::delete('media/{media}', [MediaLibraryController::class, 'destroy'])->name('media.destroy');
    Route::post('media/{media}/restore', [MediaLibraryController::class, 'restore'])->name('media.restore');
    Route::delete('media/{media}/force', [MediaLibraryController::class, 'forceDestroy'])->name('media.force-destroy');

    // CMS Pages
    Route::resource('pages', PageController::class)
        ->except(['show'])
        ->parameters(['pages' => 'page']);
    Route::get('pages/{page}/preview', [PageController::class, 'preview'])->name('pages.preview');
    Route::post('pages/{page}/restore', [PageController::class, 'restore'])->name('pages.restore');
    Route::delete('pages/{page}/force', [PageController::class, 'forceDestroy'])->name('pages.force-destroy');

    // Katalog produk: kategori
    Route::resource('product-categories', ProductCategoryController::class)
        ->except(['show'])
        ->parameters(['product-categories' => 'product_category']);
    Route::post('product-categories/{product_category}/restore', [ProductCategoryController::class, 'restore'])
        ->name('product-categories.restore');
    Route::delete('product-categories/{product_category}/force', [ProductCategoryController::class, 'forceDestroy'])
        ->name('product-categories.force-destroy');

    // Katalog produk: produk
    Route::resource('products', ProductController::class)
        ->except(['show'])
        ->parameters(['products' => 'product']);
    Route::get('products/{product}/preview', [ProductController::class, 'preview'])->name('products.preview');
    Route::post('products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('products/{product}/force', [ProductController::class, 'forceDestroy'])->name('products.force-destroy');

    // Fasilitas
    Route::resource('facilities', FacilityController::class)
        ->except(['show'])
        ->parameters(['facilities' => 'facility']);
    Route::get('facilities/{facility}/preview', [FacilityController::class, 'preview'])->name('facilities.preview');
    Route::post('facilities/{facility}/restore', [FacilityController::class, 'restore'])->name('facilities.restore');
    Route::delete('facilities/{facility}/force', [FacilityController::class, 'forceDestroy'])->name('facilities.force-destroy');

    // Galeri: kategori
    Route::resource('gallery-categories', GalleryCategoryController::class)
        ->except(['show'])
        ->parameters(['gallery-categories' => 'gallery_category']);
    Route::post('gallery-categories/{gallery_category}/restore', [GalleryCategoryController::class, 'restore'])
        ->name('gallery-categories.restore');
    Route::delete('gallery-categories/{gallery_category}/force', [GalleryCategoryController::class, 'forceDestroy'])
        ->name('gallery-categories.force-destroy');

    // Galeri: item
    Route::resource('gallery-items', GalleryItemController::class)
        ->except(['show'])
        ->parameters(['gallery-items' => 'gallery_item']);
    Route::post('gallery-items/{gallery_item}/restore', [GalleryItemController::class, 'restore'])
        ->name('gallery-items.restore');
    Route::delete('gallery-items/{gallery_item}/force', [GalleryItemController::class, 'forceDestroy'])
        ->name('gallery-items.force-destroy');
});

// Katalog publik: didaftarkan sebelum catch-all halaman agar
// /{locale}/produk tidak ditelan sebagai slug halaman.
Route::get('/{locale}/produk', [ProductCatalogController::class, 'index'])
    ->whereIn('locale', ['id', 'en'])
    ->name('public.products.index');
Route::get('/{locale}/produk/{slug}', [ProductCatalogController::class, 'show'])
    ->whereIn('locale', ['id', 'en'])
    ->name('public.products.show');

// Halaman publik CMS: dua segmen dengan locale id/en, didaftarkan terakhir
// agar tidak menelan route lain. Frontend penuh dibangun di Phase 8.
Route::get('/{locale}/{slug}', [PageShowController::class, 'show'])
    ->whereIn('locale', ['id', 'en'])
    ->name('public.pages.show');
