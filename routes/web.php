<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\CreateTestController;
use App\Http\Controllers\DescriptionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
    
    Route::get('/description', [DescriptionController::class, 'showDescription'])->name('description');
    
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index'); 
    Route::get('/tests/create', [TestController::class, 'create'])->name('tests.create');
    Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
    Route::get('/tests/{id}', [TestController::class, 'show'])->name('tests.show');
    Route::get('/tests/{id}/edit', [TestController::class, 'edit'])->name('tests.edit');
});
