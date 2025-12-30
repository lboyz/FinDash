<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\PasswordController as SettingsPasswordController;
use App\Http\Controllers\Settings\ProfileController as SettingsProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Welcome page
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => true,
    ]);
})->name('home');

// Authentication routes (guest only)
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Login
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

// OTP verification routes
Route::middleware('guest')->group(function () {
    Route::get('/verify-otp', [OTPController::class, 'show'])->name('otp.verify');
    Route::post('/verify-otp', [OTPController::class, 'verify'])
        ->middleware('throttle:otp')
        ->name('otp.verify.submit');
    Route::post('/resend-otp', [OTPController::class, 'resend'])
        ->middleware('throttle:otp')
        ->name('otp.resend');
});

// Password Reset with OTP
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOTP'])->name('password.email');
    Route::get('/reset-password', [ResetPasswordController::class, 'show'])->name('password.reset-view');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout route (authenticated users only)
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Protected routes (require auth + OTP verification)
Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureOTPVerified::class])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Profile routes moved to settings group

    // Settings routes (from settings.php)
    Route::prefix('settings')->group(function () {
        // Redirect /settings to /settings/profile
        Route::redirect('', '/settings/profile');
        Route::redirect('/', '/settings/profile');

        // Profile Settings
        Route::get('/profile', [SettingsProfileController::class, 'edit'])->name('settings.profile.edit');
        Route::patch('/profile', [SettingsProfileController::class, 'update'])->name('settings.profile.update');
        Route::delete('/profile', [SettingsProfileController::class, 'destroy'])->name('settings.profile.destroy');

        // Password Settings
        Route::get('/password', [SettingsPasswordController::class, 'edit'])->name('user-password.edit');
        Route::put('/password', [SettingsPasswordController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('user-password.update');

        // Appearance Settings
        Route::get('/appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');

        // Two Factor Authentication
        Route::get('/two-factor', [TwoFactorAuthenticationController::class, 'show'])
            ->name('two-factor.show');
    });

    // Export
    Route::get('/export/csv', [ExportController::class, 'csv'])->name('export.csv');
    Route::get('/export/pdf', [ExportController::class, 'pdf'])->name('export.pdf');
    Route::get('/export/json', [ExportController::class, 'json'])->name('export.json');

});