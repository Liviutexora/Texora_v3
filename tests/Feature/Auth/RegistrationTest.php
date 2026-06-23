<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Registration requires a SubscriptionPlan + Spatie roles to exist.
        // The free-plan path skips Stripe and creates the user directly.
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'tenant_owner', 'guard_name' => 'web']);

        // Migration 2026_05_27_000001 inserts a 'free' plan row during RefreshDatabase,
        // so use firstOrCreate to avoid a unique-slug constraint violation.
        $plan = \App\Models\SubscriptionPlan::firstOrCreate(
            ['slug' => 'free'],
            [
                'name'          => 'Free',
                'price'         => 0.00,
                'billing_cycle' => 'monthly',
                'is_active'     => true,
            ]
        );

        $response = $this->post('/register', [
            'name'                  => 'Test Business',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'plan_id'               => $plan->id,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('tenant.setup', absolute: false));
    }
}
