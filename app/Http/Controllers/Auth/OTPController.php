<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;

class OTPController extends Controller
{
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show OTP verification form.
     */
    public function show()
    {
        // Check if there's a pending OTP verification
        if (!session('otp_user_id') || !session('otp_type')) {
            return redirect()->route('login');
        }

        $user = User::find(session('otp_user_id'));
        
        return Inertia::render('auth/VerifyOTP', [
            'email' => $user->email,
            'type' => session('otp_type'),
        ]);
    }

    /**
     * Verify OTP code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('otp_user_id');
        $type = session('otp_type');

        if (!$userId || !$type) {
            return back()->withErrors(['otp' => 'Session expired. Please try again.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return back()->withErrors(['otp' => 'User not found.']);
        }

        // Verify OTP
        if (!$this->otpService->verifyOTP($user, $request->otp, $type)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP code.']);
        }

        // Mark email as verified for registration
        if ($type === 'register' && !$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        // Log the user in
        Auth::login($user, session('remember', false));

        // Clear OTP session data
        session()->forget(['otp_user_id', 'otp_type', 'remember']);

        // Mark session as OTP verified
        session(['otp_verified' => true]);

        // Set trusted device cookie for 30 days (43200 minutes)
        Cookie::queue('trusted_device_' . $user->id, true, 43200);

        return redirect()->intended(route('dashboard'))->with('success', 'Login successful!');
    }

    /**
     * Resend OTP code.
     */
    public function resend(Request $request)
    {
        $userId = session('otp_user_id');
        $type = session('otp_type');

        if (!$userId || !$type) {
            return back()->withErrors(['otp' => 'Session expired. Please try again.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return back()->withErrors(['otp' => 'User not found.']);
        }

        // Send new OTP
        $this->otpService->sendOTP($user, $type);

        return back()->with('success', 'A new OTP code has been sent to your email.');
    }
}
