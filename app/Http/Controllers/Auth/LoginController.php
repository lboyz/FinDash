<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LoginController extends Controller
{
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show the login form.
     */
    public function create()
    {
        return Inertia::render('auth/Login');
    }

    /**
     * Handle login request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find user
        $user = User::where('email', $request->email)->first();

        // Verify credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Check if OTP is enabled
        if (env('OTP_ENABLED', true)) {
            // Check for trusted device cookie
            if (Cookie::has('trusted_device_' . $user->id)) {
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                session(['otp_verified' => true]);

                return redirect()->intended(route('dashboard'));
            }

            // Send OTP
            $this->otpService->sendOTP($user, 'login');

            // Store user ID in session for OTP verification
            session([
                'otp_user_id' => $user->id,
                'otp_type' => 'login',
                'remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('otp.verify')->with('success', 'Please check your email for the OTP code.');
        }

        // If OTP is disabled, login directly
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        session(['otp_verified' => true]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle logout request.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
