<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\OTPCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->withoutTwoFactor()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // User should be redirected to OTP verification, not authenticated yet
        $this->assertGuest();
        $response->assertRedirect(route('otp.verify'));
        
        // Check that OTP was created
        $this->assertDatabaseHas('otp_codes', [
            'user_id' => $user->id,
            'type' => 'login',
            'used' => false,
        ]);
    }

    public function test_users_can_verify_otp_and_access_dashboard()
    {
        $user = User::factory()->withoutTwoFactor()->create([
            'email_verified_at' => now(),
        ]);

        // Create a valid OTP
        $otp = OTPCode::create([
            'user_id' => $user->id,
            'code' => '123456',
            'type' => 'login',
            'expired_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        // Set up session as if user just logged in
        $this->withSession([
            'otp_user_id' => $user->id,
            'otp_type' => 'login',
        ]);

        $response = $this->post(route('otp.verify.submit'), [
            'otp' => '123456',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHas('login.id', $user->id);
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['otp_verified' => true])
            ->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_users_are_rate_limited()
    {
        $user = User::factory()->create();

        RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
    }
}
