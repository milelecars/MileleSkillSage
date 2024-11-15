<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlagController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ReportPDFController;
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


Route::post('/flag', [FlagController::class, 'store'])->name('flag.store');
Route::post('/camera-permission', [CameraController::class, 'updatePermission'])->name('camera.update');
Route::get('/camera-permission', [CameraController::class, 'checkPermission'])->name('camera.check');

// Guest routes
Route::middleware('guest')->group(function () {
    // Admin registration and login routes
    Route::get('admin/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('admin/register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    // Invitation handling for guests
    Route::get('/invitation/expired', [InvitationController::class, 'expired'])->name('invitation.expired');
    Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invitation/{token}/validate', [InvitationController::class, 'validateEmail'])->name('invitation.validate');
});

// Admin authenticated routes
Route::middleware('auth:web')->group(function () {
    // Admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/description', [DescriptionController::class, 'showDescription'])->name('description');
    Route::get('/admin/candidates', [AdminController::class, 'manageCandidates'])->name('manage-candidates');
    Route::get('/admin/candidate-result/{candidate}', [AdminController::class, 'candidateResult'])->name('admin.candidate-result');
    Route::put('/admin/approve/{candidate}', [AdminController::class, 'approveCandidate'])->name('candidate.approve');
    Route::put('/admin/reject/{candidate}', [AdminController::class, 'rejectCandidate'])->name('candidate.reject');

    // Test routes for admin
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
    Route::get('/tests/create', [TestController::class, 'create'])->name('tests.create');
    Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
    Route::get('/tests/{id}/edit', [TestController::class, 'edit'])->name('tests.edit');
    Route::put('/tests/{id}', [TestController::class, 'update'])->name('tests.update');
    Route::delete('/tests/{id}', [TestController::class, 'destroy'])->name('tests.destroy');
    Route::get('/tests/{id}/invite', [TestController::class, 'invite'])->name('tests.invite');

});



// Candidate authenticated routes
Route::middleware('auth:candidate')->group(function () {
    Route::get('/candidate/dashboard', [CandidateController::class, 'dashboard'])->name('candidate.dashboard');
    Route::get('/tests/{id}/start', [TestController::class, 'startTest'])->name('tests.start');
    Route::post('/tests/{id}/next', [TestController::class, 'nextQuestion'])->name('tests.next');
    Route::post('/tests/{id}/submit', [TestController::class, 'submitTest'])->name('tests.submit');
    Route::get('/tests/{id}/result', [TestController::class, 'showResult'])->name('tests.result');
    Route::get('/reports/v1', [ReportPDFController::class, 'generateSimplePDF'])->name('reports.v1');
});

// Logout route (accessible to both admins and candidates)
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth:web,candidate');

Route::get('/tests/{id}/show', [TestController::class, 'show'])
    ->name('tests.show')
    ->middleware('auth:web,candidate');
