<?php

namespace Tests\Feature\Auth;

use App\Models\OTPCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // User should be created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'username' => 'testuser',
        ]);

        // User should NOT be authenticated until OTP is verified
        $this->assertGuest();
        
        // Should be redirected to OTP verification
        $response->assertRedirect(route('otp.verify'));

        // OTP should be created for the user
        $user = User::where('email', 'test@example.com')->first();
        $this->assertDatabaseHas('otp_codes', [
            'user_id' => $user->id,
            'type' => 'register',
            'used' => false,
        ]);
    }

    public function test_new_users_can_verify_otp_after_registration()
    {
        // Create user without email verification (simulating just registered)
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Create a valid OTP
        OTPCode::create([
            'user_id' => $user->id,
            'code' => '654321',
            'type' => 'register',
            'expired_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        // Set up session as if user just registered
        $this->withSession([
            'otp_user_id' => $user->id,
            'otp_type' => 'register',
        ]);

        $response = $this->post(route('otp.verify.submit'), [
            'otp' => '654321',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        // Email should be verified after OTP verification
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }
}
