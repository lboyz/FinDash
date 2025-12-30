<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otpCode;
    public string $type;
    public string $userName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otpCode, string $type, string $userName)
    {
        $this->otpCode = $otpCode;
        $this->type = $type;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->type) {
            'register' => 'Verify Your Registration - OTP Code',
            'login' => 'Login Verification - OTP Code',
            'password_reset' => 'Password Reset - OTP Code',
            default => 'OTP Verification Code',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->type === 'password_reset' 
            ? 'emails.password-reset-otp' 
            : 'emails.otp-email';

        return new Content(
            view: $view,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
