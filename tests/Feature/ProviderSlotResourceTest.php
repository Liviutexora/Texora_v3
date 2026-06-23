<?php

namespace Tests\Feature;

use App\Filament\Tenant\Resources\ProviderResource\Pages\ListProviders;
use App\Models\Provider;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProviderSlotResourceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('tenant'));

        // ProviderResource::getEloquentQuery() scopes by TenantContext::id().
        // Without a tenant in context it returns whereRaw('0 = 1') — no records.
        $plan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'free'],
            ['name' => 'Free', 'price' => 0, 'billing_cycle' => 'monthly', 'is_active' => true]
        );

        $owner = User::factory()->create();

        $this->tenant = Tenant::create([
            'name'     => 'Test Studio',
            'slug'     => 'test-studio',
            'owner_id' => $owner->id,
            'plan_id'  => $plan->id,
            'status'   => 'active',
        ]);

        TenantContext::set($this->tenant);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    public function test_provider_sees_only_own_slot_record(): void
    {
        $providerUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $providerUser->assignRole('provider');
        $provider = Provider::factory()->create([
            'user_id'   => $providerUser->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $otherProviderUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $otherProviderUser->assignRole('provider');
        Provider::factory()->create([
            'user_id'   => $otherProviderUser->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($providerUser);

        Livewire::test(ListProviders::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$provider])
            ->assertCountTableRecords(1);
    }

    public function test_super_admin_sees_all_provider_slots(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $d1 = Provider::factory()->create(['tenant_id' => $this->tenant->id]);
        $d2 = Provider::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($superAdmin);

        Livewire::test(ListProviders::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$d1, $d2])
            ->assertCountTableRecords(2);
    }
}
