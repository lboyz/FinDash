<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ResetPasswordController extends Controller
{
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show reset password form with OTP input.
     */
    public function show()
    {
        // Check if there's a pending password reset
        if (!session('password_reset_user_id') || !session('password_reset_email')) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Please enter your email first.',
            ]);
        }

        return Inertia::render('auth/ResetPasswordOTP', [
            'email' => session('password_reset_email'),
        ]);
    }

    /**
     * Verify OTP and reset password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $userId = session('password_reset_user_id');
        $email = session('password_reset_email');

        if (!$userId || !$email) {
            return back()->withErrors(['otp' => 'Session expired. Please try again.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return back()->withErrors(['otp' => 'User not found.']);
        }

        // Verify OTP
        if (!$this->otpService->verifyOTP($user, $request->otp, 'password_reset')) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP code.']);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear session
        session()->forget(['password_reset_user_id', 'password_reset_email']);

        return redirect()->route('login')->with('status', 'Password has been reset successfully. Please login with your new password.');
    }
}
