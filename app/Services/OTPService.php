<?php

namespace App\Services;

use App\Models\OTPCode;
use App\Models\User;
use App\Mail\OTPMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OTPService
{
    /**
     * Generate a 6-digit OTP code.
     */
    public function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create and send OTP to user's email.
     */
    public function sendOTP(User $user, string $type): OTPCode
    {
        // Clean up old unused OTPs for this user and type
        $this->cleanupOldOTPs($user, $type);

        // Generate new OTP
        $code = $this->generateCode();
        $expiredAt = Carbon::now()->addMinutes(10);

        // Create OTP record
        $otp = OTPCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => $type,
            'expired_at' => $expiredAt,
            'used' => false,
        ]);

        // Send email
        Mail::to($user->email)->send(new OTPMail($code, $type, $user->name));

        return $otp;
    }

    /**
     * Verify OTP code.
     */
    public function verifyOTP(User $user, string $code, string $type): bool
    {
        $otp = OTPCode::where('user_id', $user->id)
            ->where('code', $code)
            ->where('type', $type)
            ->valid()
            ->first();

        if (!$otp) {
            return false;
        }

        // Mark as used
        $otp->markAsUsed();

        return true;
    }

    /**
     * Clean up old unused OTPs for a user.
     */
    public function cleanupOldOTPs(User $user, string $type): void
    {
        OTPCode::where('user_id', $user->id)
            ->where('type', $type)
            ->where('used', false)
            ->delete();
    }

    /**
     * Clean up all expired OTPs (can be run via scheduled task).
     */
    public function cleanupExpiredOTPs(): void
    {
        OTPCode::where('expired_at', '<', Carbon::now())->delete();
    }
}
