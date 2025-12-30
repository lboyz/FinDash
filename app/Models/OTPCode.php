<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OTPCode extends Model
{
    use HasFactory;

    protected $table = 'otp_codes';

    protected $fillable = [
        'user_id',
        'code',
        'type',
        'expired_at',
        'used',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Get the user that owns the OTP code.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include valid OTP codes.
     */
    public function scopeValid($query)
    {
        return $query->where('used', false)
                     ->where('expired_at', '>', Carbon::now());
    }

    /**
     * Scope a query to only include unused OTP codes.
     */
    public function scopeUnused($query)
    {
        return $query->where('used', false);
    }

    /**
     * Check if the OTP code is expired.
     */
    public function isExpired(): bool
    {
        return $this->expired_at < Carbon::now();
    }

    /**
     * Mark the OTP code as used.
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }
}
