<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    private function makeTenant(): Tenant
    {
        $owner = User::forceCreate([
            'name'              => 'Owner',
            'email'             => 'owner' . uniqid() . '@example.com',
            'password'          => bcrypt('pass'),
            'email_verified_at' => now(),
        ]);

        return Tenant::create([
            'name'     => 'Test Clinic',
            'slug'     => 'test-business-' . uniqid(),
            'email'    => 'business' . uniqid() . '@example.com',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $owner->id,
        ]);
    }

    public function test_is_not_set_by_default(): void
    {
        $this->assertFalse(TenantContext::isSet());
        $this->assertNull(TenantContext::current());
        $this->assertNull(TenantContext::id());
    }

    public function test_set_makes_tenant_available(): void
    {
        $tenant = $this->makeTenant();

        TenantContext::set($tenant);

        $this->assertTrue(TenantContext::isSet());
        $this->assertSame($tenant->id, TenantContext::current()->id);
    }

    public function test_id_returns_tenant_primary_key(): void
    {
        $tenant = $this->makeTenant();

        TenantContext::set($tenant);

        $this->assertSame($tenant->id, TenantContext::id());
    }

    public function test_clear_resets_context(): void
    {
        $tenant = $this->makeTenant();
        TenantContext::set($tenant);

        TenantContext::clear();

        $this->assertFalse(TenantContext::isSet());
        $this->assertNull(TenantContext::current());
        $this->assertNull(TenantContext::id());
    }

    public function test_set_replaces_existing_tenant(): void
    {
        $tenantA = $this->makeTenant();
        $tenantB = $this->makeTenant();

        TenantContext::set($tenantA);
        TenantContext::set($tenantB);

        $this->assertSame($tenantB->id, TenantContext::id());
    }
}
