<?php

use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
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
});
