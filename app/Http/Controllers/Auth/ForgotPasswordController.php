<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show forgot password form.
     */
    public function show()
    {
        return Inertia::render('auth/ForgotPasswordOTP');
    }

    /**
     * Send OTP to email for password reset.
     */
    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->onlyInput('email');
        }

        // Send OTP for password reset
        $this->otpService->sendOTP($user, 'password_reset');

        // Store user ID in session for password reset
        session([
            'password_reset_user_id' => $user->id,
            'password_reset_email' => $user->email,
        ]);

        return redirect()->route('password.reset-view')->with('success', 'OTP code has been sent to your email.');
    }
}
