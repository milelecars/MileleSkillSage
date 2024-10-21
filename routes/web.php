<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CreateTestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\DescriptionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Root route
Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('admin.dashboard');
    } elseif (Auth::guard('candidate')->check()) {
        return redirect()->route('candidate.dashboard');
    } else {
        return redirect()->route('welcome');
    }
});

// Welcome route
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');
 

// Guest routes
Route::middleware('guest')->group(function () {
    // Admin registration and login routes
    Route::get('admin/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('admin/register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    // Invitation handling for guests
    Route::get('/invitation/expired', [InvitationController::class, 'expired'])->name('invitation.expired');
    Route::get('/invitation/{invitationLink}', [InvitationController::class, 'show'])->name('invitation.show');
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
    Route::get('/tests/{id}/show', [TestController::class, 'show'])->name('tests.show');
    Route::get('/tests/{id}/edit', [TestController::class, 'edit'])->name('tests.edit');
    Route::put('/tests/{id}', [TestController::class, 'update'])->name('tests.update');
    // Route::get('/tests/{id}', [TestController::class, 'show'])->name('tests.show');
    Route::delete('/tests/{id}', [TestController::class, 'destroy'])->name('tests.destroy');
    Route::get('/tests/{id}/invite', [TestController::class, 'invite'])->name('tests.invite');

});

// Candidate authenticated routes
Route::middleware('auth:candidate')->group(function () {
    Route::get('/candidate/dashboard', [CandidateController::class, 'dashboard'])->name('candidate.dashboard');
    // Route::get('/tests/{id}', [TestController::class, 'show'])->name('tests.show');
    Route::get('/tests/{id}/show', [TestController::class, 'show'])->name('tests.show');
    Route::get('/tests/{id}/start', [TestController::class, 'startTest'])->name('tests.start');
    Route::post('/tests/{id}/next', [TestController::class, 'nextQuestion'])->name('tests.next');
    Route::post('/tests/{id}/submit', [TestController::class, 'submitTest'])->name('tests.submit');
    Route::get('/tests/{id}/result', [TestController::class, 'showResult'])->name('tests.result');
});

// Logout route (accessible to both admins and candidates)
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth:web,candidate');