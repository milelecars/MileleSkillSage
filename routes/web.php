<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CreateTestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\DescriptionController;
use App\Http\Controllers\TemporaryAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    return view('welcome');
});

// Guest routes
Route::middleware('guest')->group(function () {
    // Admin registration (only accessible by guests)
    Route::get('admin/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('admin/register', [RegisteredUserController::class, 'store']);

    // Login routes (for both admin and candidates)
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/invitation/expired', [InvitationController::class, 'expired'])->name('invitation.expired');
    Route::get('/invitation/{invitationLink}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invitation/{invitationLink}/validate', [InvitationController::class, 'validateEmail'])->name('invitation.validate');

});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/description', [DescriptionController::class, 'showDescription'])->name('description');
    
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index'); 
    Route::get('/tests/create', [TestController::class, 'create'])->name('tests.create');
    Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
    Route::get('/tests/{id}', [TestController::class, 'show'])->name('tests.show');
    Route::get('/tests/{id}/edit', [TestController::class, 'edit'])->name('tests.edit');

    // Candidate routes
    Route::get('/candidate/dashboard', [CandidateController::class, 'dashboard'])->name('candidate.dashboard');
    Route::get('/candidate/test', [CandidateController::class, 'startTest'])->name('candidate.test');

    // Temporary token routes
    // Route::post('/send-token', [TemporaryAuthController::class, 'sendToken'])->name('send.token');
    // Route::get('/login/{token}', [TemporaryAuthController::class, 'login'])->name('login.token');
});