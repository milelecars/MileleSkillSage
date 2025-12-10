<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlagController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ReportPDFController;
use App\Http\Controllers\CreateTestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\DescriptionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\Auth\RegisteredAdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\ExcelExportController;

Route::get('/google/login/{testId}', [OAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/oauth2/callback', [OAuthController::class, 'handleGoogleCallback']);

// Root route
Route::get('/', function () {
    return redirect()->route('welcome');
});

// Welcome route
Route::get('/welcome', function () {
    if (Auth::guard('web')->check()) {
        Auth::guard('web')->logout();
        session()->regenerateToken();
    }
    return view('welcome');
})->name('welcome');

Route::get('/check', function() {
    dd(Auth::check(), Auth::guard('web')->check(), Auth::guard('candidate')->check());
});
Route::post('/flag', [FlagController::class, 'store'])->name('flag.store');
Route::post('/camera-permission', [CameraController::class, 'updatePermission']);
Route::get('/camera-permission', [CameraController::class, 'checkPermission']);
Route::post('/api/screenshots', [TestController::class, 'saveScreenshot'])->name('screenshots.save');

// Guest routes
Route::middleware('guest')->group(function () {
    // Admin registration and login routes
    // Route::get('admin/register', [RegisteredAdminController::class, 'create'])->name('register');
    // Route::post('admin/register', [RegisteredAdminController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Invitation handling for guests
    Route::get('/invitation/expired', [InvitationController::class, 'expired'])->name('invitation.expired');
    Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invitation/{token}/validate', [InvitationController::class, 'validateEmail'])->name('invitation.validate');
});

Route::middleware(['guest', 'throttle:3,5'])->group(function () {
    Route::post('generate-otp', [AuthenticatedSessionController::class, 'generateOtp'])
        ->name('generate.otp');
});

// Admin authenticated routes
Route::middleware('auth:web')->group(function () {
    // Admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/description', [DescriptionController::class, 'showDescription'])->name('description');
    Route::get('/admin/manage-candidates', [AdminController::class, 'manageCandidates'])->name('admin.manage-candidates');
    Route::get('/admin/candidate-result/{test}/{candidate}', [AdminController::class, 'candidateResult'])
    ->name('admin.candidate-result');
    Route::get('private-screenshot/{testId}/{candidateId}/{filename}', [AdminController::class, 'getPrivateScreenshot'])
    ->name('private.screenshot');
        
    Route::get('/admin/export-candidates', [ExcelExportController::class, 'exportCandidates'])
    ->name('admin.export-candidates');
    Route::post('/admin/unsuspend-test/{candidateId}/{testId}', [AdminController::class, 'unsuspendTest'])->name('admin.unsuspend-test');
    Route::put('/candidates/{candidate}/accept', [AdminController::class, 'acceptCandidate'])
    ->name('candidate.accept');
    Route::put('/candidates/{candidate}/reject', [AdminController::class, 'rejectCandidate'])
    ->name('candidate.reject');
    Route::delete('/candidates/{candidateId}/{testId}/delete', [AdminController::class, 'deleteCandidate'])
    ->name('candidate.delete');
    Route::post('/invitation/extend-deadline', [InvitationController::class, 'extendDeadline'])
    ->name('invitations.extend-deadline');     

    Route::get('/admin/invite', [AdminController::class, 'inviteCandidate'])->name('admin.invite');
    Route::post('/departments/store', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/search', [DepartmentController::class, 'search'])->name('departments.search');
    Route::get('/admin/select-candidate', [AdminController::class, 'inviteCandidate'])
    ->name('admin.select-candidate');
    Route::post('/admin/send', [AdminController::class, 'sendInvitation'])
    ->name('admin.send');

    Route::get('/reports/candidate-report/{candidateId}/{testId}', [ReportPDFController::class, 'streamPDF'])->name('reports.candidate-report');
    Route::get('/reports/error', [ReportPDFController::class, 'showErrorPage'])->name('reports.error');

    Route::get('/admin/manage-reports', [AdminController::class, 'manageReports'])->name('admin.manage-reports');
    Route::get('/admin/manage-reports/download/{testId}', [AdminController::class, 'downloadTestReports'])->name('admin.download-test-reports');
    Route::get('/private-screenshot/{testId}/{candidateId}/{filename}', [ScreenshotController::class, 'show'])
    ->name('private.screenshot')
    ->where('filename', '.*');
    
    // Test routes for admin
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
    Route::get('/tests/create', [TestController::class, 'create'])->name('tests.create');
    Route::post('/tests', [TestController::class, 'store'])->name('tests.store');
    Route::get('/tests/{id}/edit', [TestController::class, 'edit'])->name('tests.edit');
    Route::put('/tests/{id}', [TestController::class, 'update'])->name('tests.update');
    Route::delete('/tests/{id}', [TestController::class, 'destroy'])->name('tests.destroy'); // Archives a test
    Route::get('/tests/archived', [TestController::class, 'archived'])->name('tests.archived');
    Route::patch('/tests/{id}/restore', [TestController::class, 'restore'])->name('tests.restore'); 
    Route::get('/tests/{id}/invite', [TestController::class, 'invite'])->name('tests.invite');
    Route::get('/tests/{id}/show', [TestController::class, 'show'])->name('tests.show');

    Route::get('/admin/access-control', [AccessController::class, 'index'])->name('admin.access-control');
    Route::post('/admin/access-control', [AccessController::class, 'store'])->name('admin.access-control.store');
    Route::put('/admin/access-control/{admin}', [AccessController::class, 'update'])->name('admin.access-control.update');
    Route::delete('/admin/access-control/{admin}', [AccessController::class, 'destroy'])->name('admin.access-control.destroy');


});


// Candidate authenticated routes
Route::middleware('auth:candidate')->group(function () {
    Route::get('/candidate/dashboard', [CandidateController::class, 'dashboard'])->name('candidate.dashboard');
    Route::get('/tests/{id}/setup', [TestController::class, 'setup'])->name('tests.setup');
    Route::match(['get', 'post'], '/tests/{id}/start', [TestController::class, 'startTest'])->name('tests.start');
    Route::post('/tests/{id}/next', [TestController::class, 'nextQuestion'])->name('tests.next');
    Route::match(['get', 'post'], '/tests/{id}/submit', [TestController::class, 'submitTest'])->name('tests.submit');
    Route::get('/tests/{id}/result', [TestController::class, 'showResult'])->name('tests.result');
    Route::post('/candidate-flags', [FlagController::class, 'store'])->name('candidate-flags.store'); 
    Route::get('/tests/{testId}/suspended', [TestController::class, 'showSuspended'])->name('tests.suspended');
    Route::post('/tests/{testId}/request-unsuspension', [TestController::class, 'requestUnsuspension'])->name('tests.request-unsuspension');
    Route::post('/log-suspension', [TestController::class, 'logSuspension'])->name('tests.log-suspension');
    Route::post('/get-unsuspend-count', [TestController::class, 'getUnsuspendCount']);
    Route::get('/tests/{id}/show', [TestController::class, 'show'])->name('tests.show');

});

// Logout route (accessible to both admins and candidates)
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth:web,candidate');

