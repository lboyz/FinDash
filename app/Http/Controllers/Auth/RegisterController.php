<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class RegisterController extends Controller
{
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show the registration form.
     */
    public function create()
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle registration request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Create user (unverified)
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Send OTP
        $this->otpService->sendOTP($user, 'register');

        // Store user ID in session for OTP verification
        session([
            'otp_user_id' => $user->id,
            'otp_type' => 'register',
        ]);

        return redirect()->route('otp.verify')->with('success', 'Registration successful! Please check your email for the OTP code.');
    }
}
