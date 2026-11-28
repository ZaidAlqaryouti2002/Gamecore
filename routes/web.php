<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CustomerAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::post('/logout', [CustomerAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::view('/dashboard', 'admin.dashboard')
        ->middleware(['auth', 'admin'])
        ->name('dashboard');

    Route::view('/profile', 'admin.profile')
        ->middleware(['auth', 'admin'])
        ->name('profile');

    Route::view('/categories', 'admin.categories.index')
        ->middleware(['auth', 'admin'])
        ->name('categories.index');

    foreach (['products', 'users', 'orders', 'reviews'] as $resource) {
        Route::view('/'.$resource, 'admin.resources.index', ['resource' => $resource])
            ->middleware(['auth', 'admin'])
            ->name($resource.'.index');
    }
});
