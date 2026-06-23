<?php

namespace Tests\Feature\Auth;

use App\Events\ForgotPasswordRequested;
use App\Jobs\SendForgotPasswordEmail;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithResetPermission(): User
    {
        $user = User::factory()->create();

        NotificationPreference::create([
            'user_id'          => $user->id,
            'permission_name'  => 'forgot_password',
            'email'            => true,
            'sms'              => false,
            'web_notification' => false,
        ]);

        return $user;
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Event::fake([ForgotPasswordRequested::class]);

        $user = $this->createUserWithResetPermission();

        $this->post('/forgot-password', ['email' => $user->email]);

        Event::assertDispatched(ForgotPasswordRequested::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_reset_password_email_job_is_dispatched(): void
    {
        Queue::fake();

        $user = $this->createUserWithResetPermission();

        $this->post('/forgot-password', ['email' => $user->email]);

        Queue::assertPushed(SendForgotPasswordEmail::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = $this->createUserWithResetPermission();

        $token = app('auth.password.broker')->createToken($user);

        $response = $this->get('/reset-password/' . $token);

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = $this->createUserWithResetPermission();

        $token = app('auth.password.broker')->createToken($user);

        $response = $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));
    }
}
