<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CreateTestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\DescriptionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Guest routes
Route::middleware('guest')->group(function () {
    // Admin registration and login routes
    Route::get('admin/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('admin/register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Invitation handling for guests
    Route::get('/invitation/expired', [InvitationController::class, 'expired'])->name('invitation.expired');
    Route::get('/invitation/{invitationLink}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::get('/invitation/candidate-auth', [InvitationController::class, 'show'])->name('invitation.candidate-auth');
    Route::post('/invitation/{invitationLink}/validate', [InvitationController::class, 'validateEmail'])->name('invitation.validate');
});

// Admin authenticated routes
Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/description', [DescriptionController::class, 'showDescription'])->name('description');

    // Test routes for admins
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
    Route::get('/tests/create', [TestController::class, 'create'])->name('tests.create');
    Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
    Route::get('/tests/{id}/edit', [TestController::class, 'edit'])->name('tests.edit');
});

// Candidate authenticated routes
Route::middleware('auth:candidate')->group(function () {
    Route::get('/candidate/dashboard', [CandidateController::class, 'dashboard'])->name('candidate.dashboard');
    Route::get('/tests/{id}/start', [TestController::class, 'startTest'])->name('tests.start');
});
