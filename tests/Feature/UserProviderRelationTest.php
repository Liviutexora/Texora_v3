<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserProviderRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_super_admin_can_load_edit_page_for_provider_user(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $providerUser = User::factory()->create();
        $providerUser->assignRole('provider');

        $this->actingAs($superAdmin);

        Livewire::test(EditUser::class, [
            'record' => $providerUser->getRouteKey(),
        ])
            ->assertOk()
            ->assertSet('data.name', $providerUser->name);
    }

    public function test_super_admin_can_load_edit_page_for_client_user(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $clientUser = User::factory()->create();
        $clientUser->assignRole('client');

        $this->actingAs($superAdmin);

        Livewire::test(EditUser::class, [
            'record' => $clientUser->getRouteKey(),
        ])
            ->assertOk()
            ->assertSet('data.name', $clientUser->name);
    }

    public function test_delete_action_unavailable_for_super_admin_record(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin);

        // Editing the super admin themselves — delete should be absent from header actions
        $component = Livewire::test(EditUser::class, [
            'record' => $superAdmin->getRouteKey(),
        ])->assertOk();

        // The component renders without a delete button for super_admin
        $component->assertDontSeeHtml('id="actions-delete-action"');
    }
}
