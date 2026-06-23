<?php

namespace Tests\Feature;

use App\Livewire\Tenant\FormBuilder;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User   $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user without a tenant first (tenant_id is set after tenant is created)
        $this->user = User::forceCreate([
            'name'              => 'Test User',
            'email'             => 'user@example.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->tenant = Tenant::create([
            'name'     => 'Test Clinic',
            'slug'     => 'test-business',
            'email'    => 'business@example.com',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'owner_id' => $this->user->id,
        ]);

        // Link user back to tenant
        \DB::table('users')->where('id', $this->user->id)->update(['tenant_id' => $this->tenant->id]);
        $this->user->refresh();

        TenantContext::set($this->tenant);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }


    // ── helpers ──────────────────────────────────────────────────────

    /**
     * Returns a FormBuilder component with the default seed fields cleared.
     * FormBuilder seeds 4 default fields on first mount (when custom_fields is empty).
     * Tests that verify add/delete/reorder behaviour need a blank slate.
     */
    private function freshComponent(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(FormBuilder::class)
            ->set('fields', [])
            ->set('isDirty', false);
    }

    // ── mount ────────────────────────────────────────────────────────

    public function test_mounts_with_tenant_id_and_form_title(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->assertSet('tenantId', $this->tenant->id)
            ->assertSet('formTitle', 'Test Clinic Booking Form')
            ->assertOk();
    }

    public function test_mounts_with_existing_custom_fields(): void
    {
        $this->tenant->update(['custom_fields' => [
            ['id' => 'f001', 'type' => 'short_text', 'label' => 'Full name', 'placeholder' => '', 'required' => true, 'hidden' => false, 'options' => []],
        ]]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->assertSet('fields', function ($fields) {
                return count($fields) === 1 && $fields[0]['label'] === 'Full name';
            });
    }

    public function test_mounts_loading_success_page_settings(): void
    {
        Setting::set("tenant_{$this->tenant->id}_success_title", 'Appointment confirmed!', 'tenant');
        Setting::set("tenant_{$this->tenant->id}_success_body",  'See you tomorrow.', 'tenant');
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->assertSet('successTitle', 'Appointment confirmed!')
            ->assertSet('successBody',  'See you tomorrow.');
    }

    // ── tabs ─────────────────────────────────────────────────────────

    public function test_set_tab_changes_active_tab(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('setTab', 'services')
            ->assertSet('activeTab', 'services');
    }

    public function test_set_tab_clears_selected_field(): void
    {
        $this->actingAs($this->user);

        $this->freshComponent()
            ->call('addField', 'email')
            ->tap(function ($component) {
                $component->call('selectField', $component->get('selectedId'));
            })
            ->call('setTab', 'themes')
            ->assertSet('selectedId', null);
    }

    // ── field management ─────────────────────────────────────────────

    public function test_add_field_appends_and_selects_it(): void
    {
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'email');

        $fields = $component->get('fields');
        $this->assertCount(1, $fields);
        $this->assertSame('email', $fields[0]['type']);
        $this->assertSame($fields[0]['id'], $component->get('selectedId'));
        $component->assertSet('isDirty', true);
    }

    public function test_add_field_populates_defaults(): void
    {
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'email');

        $field = $component->get('fields')[0];
        $this->assertSame('Email address', $field['label']);
        $this->assertSame('you@email.com', $field['placeholder']);
    }

    public function test_delete_field_removes_it(): void
    {
        // deleteField blocks removal when only 1 field remains — need 2 to delete one.
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'short_text')
            ->call('addField', 'email');

        $id = $component->get('fields')[0]['id'];

        $component->call('deleteField', $id);
        $this->assertCount(1, $component->get('fields'));
        $this->assertSame('email', $component->get('fields')[0]['type']);
    }

    public function test_delete_selected_field_clears_selection(): void
    {
        // Need a second field so the guard (count <= 1) allows deletion.
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'phone')
            ->call('addField', 'email');

        // Select the first field and delete it.
        $id = $component->get('fields')[0]['id'];
        $component->set('selectedId', $id)
            ->call('deleteField', $id)
            ->assertSet('selectedId', null);
    }

    public function test_select_field_sets_selection(): void
    {
        // selectField always selects — there is no toggle; deselection is via tab changes.
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'dropdown')
            ->call('addField', 'email');

        $firstId  = $component->get('fields')[0]['id'];
        $secondId = $component->get('fields')[1]['id'];

        $component->set('selectedId', null)
            ->call('selectField', $firstId)
            ->assertSet('selectedId', $firstId)
            ->call('selectField', $secondId)
            ->assertSet('selectedId', $secondId);
    }

    public function test_reorder_changes_field_order(): void
    {
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'short_text')
            ->call('addField', 'email');

        [$first, $second] = collect($component->get('fields'))->pluck('id')->all();

        $component->call('reorder', [$second, $first]);
        $ids = collect($component->get('fields'))->pluck('id')->all();
        $this->assertSame([$second, $first], $ids);
    }

    // ── property editing ─────────────────────────────────────────────

    public function test_update_prop_changes_field_label(): void
    {
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'short_text');

        $id = $component->get('fields')[0]['id'];
        $component->call('updateProp', $id, 'label', 'Your full name');

        $this->assertSame('Your full name', $component->get('fields')[0]['label']);
    }

    public function test_update_prop_sets_required(): void
    {
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'short_text');

        $id = $component->get('fields')[0]['id'];
        $component->call('updateProp', $id, 'required', true);

        $this->assertTrue($component->get('fields')[0]['required']);
    }

    public function test_add_option_appends_to_dropdown(): void
    {
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'dropdown');

        $id = $component->get('fields')[0]['id'];
        $initialCount = count($component->get('fields')[0]['options']);
        $component->call('addOption', $id);
        $this->assertCount($initialCount + 1, $component->get('fields')[0]['options']);
    }

    public function test_remove_option_removes_it(): void
    {
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'dropdown');

        $id = $component->get('fields')[0]['id'];
        $component->call('removeOption', $id, 0);
        $options = $component->get('fields')[0]['options'];
        $this->assertCount(1, $options);
    }

    // ── undo / redo ──────────────────────────────────────────────────

    public function test_undo_reverts_last_change(): void
    {
        $this->actingAs($this->user);

        $this->freshComponent()
            ->call('addField', 'email')
            ->call('undo')
            ->assertSet('fields', []);
    }

    public function test_redo_reapplies_undone_change(): void
    {
        $this->actingAs($this->user);

        $component = $this->freshComponent()
            ->call('addField', 'email')
            ->call('undo')
            ->call('redo');

        $this->assertCount(1, $component->get('fields'));
    }

    public function test_undo_on_empty_history_is_noop(): void
    {
        $this->actingAs($this->user);

        $this->freshComponent()
            ->call('undo')
            ->assertSet('fields', [])
            ->assertSet('isDirty', false);
    }

    // ── save / discard ───────────────────────────────────────────────

    public function test_save_persists_fields_to_tenant(): void
    {
        $this->actingAs($this->user);

        $this->freshComponent()
            ->call('addField', 'email')
            ->call('save');

        $stored = Tenant::find($this->tenant->id)->custom_fields;
        $this->assertCount(1, $stored);
        $this->assertSame('email', $stored[0]['type']);
    }

    public function test_save_clears_dirty_flag_and_dispatches_event(): void
    {
        $this->actingAs($this->user);

        $this->freshComponent()
            ->call('addField', 'phone')
            ->call('save')
            ->assertSet('isDirty', false)
            ->assertDispatched('form-builder-saved');
    }

    public function test_discard_reverts_unsaved_changes(): void
    {
        $this->actingAs($this->user);

        $this->freshComponent()
            ->call('addField', 'email')
            ->call('discard')
            ->assertSet('fields', [])
            ->assertSet('isDirty', false);
    }

    // ── theme ─────────────────────────────────────────────────────────

    public function test_save_theme_persists_color(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->set('bookingColor', '#3b82f6')
            ->call('saveTheme')
            ->assertDispatched('form-builder-saved');

        $this->assertSame('#3b82f6', Tenant::find($this->tenant->id)->booking_page_color);
    }

    public function test_save_theme_persists_booking_theme(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->set('bookingTheme', 'lumina')
            ->call('saveTheme')
            ->assertDispatched('form-builder-saved');

        $this->assertSame('lumina', Setting::get("tenant_{$this->tenant->id}_booking_theme"));
    }

    public function test_save_theme_persists_date_picker_style(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->set('datePickerStyle', 'weekly')
            ->call('saveTheme')
            ->assertDispatched('form-builder-saved');

        $this->assertSame('weekly', Setting::get("tenant_{$this->tenant->id}_date_picker_style"));
    }

    // ── success page ─────────────────────────────────────────────────

    public function test_save_success_page_persists_settings(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->set('successTitle', 'Great, you are booked!')
            ->set('successBody',  'See you at the office.')
            ->call('saveSuccessPage')
            ->assertDispatched('form-builder-saved');

        $this->assertSame('Great, you are booked!', Setting::get("tenant_{$this->tenant->id}_success_title"));
        $this->assertSame('See you at the office.',  Setting::get("tenant_{$this->tenant->id}_success_body"));
    }

    public function test_save_success_page_persists_in_demo_mode(): void
    {
        // Register the same eloquent.saving block AppServiceProvider applies in DEMO_MODE.
        // If saveSuccessPage() ever reverts to Setting::set() (Eloquent), this test will fail.
        app('events')->listen('eloquent.saving: *', fn () => false);

        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->set('successTitle', 'Saved Via DB Table')
            ->set('successBody',  'DEMO_MODE bypass confirmed')
            ->call('saveSuccessPage')
            ->assertDispatched('form-builder-saved');

        // Assert directly in DB — bypasses the cache that Setting::get() uses
        $saved = \DB::table('settings')
            ->where('key', "tenant_{$this->tenant->id}_success_title")
            ->value('value');
        $this->assertSame('Saved Via DB Table', $saved);
    }

    // ── settings ─────────────────────────────────────────────────────

    public function test_save_settings_updates_tenant(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->set('tenantName',     'New Clinic Name')
            ->set('tenantEmail',    'new@example.com')
            ->set('tenantPhone',    '+10000000000')
            ->set('tenantTimezone', 'America/New_York')
            ->set('tenantCurrency', 'GBP')
            ->call('saveSettings')
            ->assertDispatched('form-builder-saved');

        $tenant = Tenant::find($this->tenant->id);
        $this->assertSame('New Clinic Name',   $tenant->name);
        $this->assertSame('new@example.com',   $tenant->email);
        $this->assertSame('America/New_York',  $tenant->timezone);
        $this->assertSame('GBP',               $tenant->currency);
    }

    public function test_save_settings_rejects_duplicate_slug(): void
    {
        Tenant::create(['name' => 'Other', 'slug' => 'taken-slug', 'timezone' => 'UTC', 'currency' => 'USD', 'owner_id' => $this->user->id]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->set('tenantSlugEdit', 'taken-slug')
            ->call('saveSettings')
            ->assertSet('settingsError', 'This URL slug is already taken.');
    }

    // ── service modal ─────────────────────────────────────────────────

    public function test_open_service_create_shows_modal_with_defaults(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openServiceCreate')
            ->assertSet('showServiceModal', true)
            ->assertSet('editingServiceId', null)
            ->assertSet('svcName', '');
    }

    public function test_save_service_creates_service(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openServiceCreate')
            ->set('svcName',     'Haircut')
            ->set('svcDuration', 45)
            ->set('svcPrice',    25.00)
            ->call('saveService')
            ->assertSet('showServiceModal', false)
            ->assertDispatched('form-builder-saved');

        $this->assertDatabaseHas('services', [
            'tenant_id'        => $this->tenant->id,
            'name'             => 'Haircut',
            'duration_minutes' => 45,
        ]);
    }

    public function test_save_service_ignores_empty_name(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openServiceCreate')
            ->set('svcName', '   ')
            ->call('saveService')
            ->assertSet('showServiceModal', true);

        $this->assertDatabaseCount('services', 0);
    }

    public function test_open_service_edit_loads_service_data(): void
    {
        $svc = Service::create([
            'tenant_id'        => $this->tenant->id,
            'name'             => 'Massage',
            'duration_minutes' => 60,
            'price'            => 80,
            'currency'         => 'USD',
            'is_active'        => true,
        ]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openServiceEdit', $svc->id)
            ->assertSet('editingServiceId', $svc->id)
            ->assertSet('svcName',          'Massage')
            ->assertSet('svcDuration',      60)
            ->assertSet('showServiceModal', true);
    }

    public function test_save_service_updates_existing(): void
    {
        $svc = Service::create([
            'tenant_id'        => $this->tenant->id,
            'name'             => 'Old name',
            'duration_minutes' => 30,
            'price'            => 0,
            'currency'         => 'USD',
            'is_active'        => true,
        ]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openServiceEdit', $svc->id)
            ->set('svcName', 'New name')
            ->call('saveService');

        $this->assertDatabaseHas('services', ['id' => $svc->id, 'name' => 'New name']);
    }

    public function test_delete_service_removes_it(): void
    {
        $svc = Service::create([
            'tenant_id'        => $this->tenant->id,
            'name'             => 'To delete',
            'duration_minutes' => 30,
            'price'            => 0,
            'currency'         => 'USD',
            'is_active'        => true,
        ]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('deleteService', $svc->id);

        $this->assertDatabaseMissing('services', ['id' => $svc->id]);
    }

    public function test_service_edit_restricted_to_own_tenant(): void
    {
        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'timezone' => 'UTC', 'currency' => 'USD', 'owner_id' => $this->user->id]);
        $svc   = Service::create([
            'tenant_id'        => $other->id,
            'name'             => 'Foreign',
            'duration_minutes' => 30,
            'price'            => 0,
            'currency'         => 'USD',
            'is_active'        => true,
        ]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openServiceEdit', $svc->id)
            ->assertSet('showServiceModal', false);
    }

    // ── provider modal ────────────────────────────────────────────────

    public function test_open_provider_create_shows_modal(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openProviderCreate')
            ->assertSet('showProviderModal', true)
            ->assertSet('editingProviderId', null);
    }

    public function test_save_provider_requires_user_id(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openProviderCreate')
            ->set('prvUserId', null)
            ->call('saveProvider')
            ->assertSet('showProviderModal', true);

        $this->assertDatabaseCount('providers', 0);
    }

    public function test_save_provider_creates_provider(): void
    {
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openProviderCreate')
            ->set('prvUserId',    $this->user->id)
            ->set('prvJobTitle',  'Senior Stylist')
            ->set('prvExperience','5')
            ->call('saveProvider')
            ->assertSet('showProviderModal', false)
            ->assertDispatched('form-builder-saved');

        $this->assertDatabaseHas('providers', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->user->id,
            'job_title' => 'Senior Stylist',
        ]);
    }

    public function test_open_provider_edit_loads_data(): void
    {
        $provider = Provider::create([
            'tenant_id'        => $this->tenant->id,
            'user_id'          => $this->user->id,
            'job_title'        => 'Nurse',
            'experience_years' => 3,
            'bio'              => 'Test bio',
        ]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openProviderEdit', $provider->id)
            ->assertSet('editingProviderId', $provider->id)
            ->assertSet('prvJobTitle',       'Nurse')
            ->assertSet('showProviderModal', true);
    }

    public function test_delete_provider_removes_provider(): void
    {
        $provider = Provider::create([
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->user->id,
        ]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('deleteProvider', $provider->id);

        $this->assertDatabaseMissing('providers', ['id' => $provider->id]);
    }

    public function test_provider_edit_restricted_to_own_tenant(): void
    {
        $user2    = User::forceCreate(['name' => 'Other User', 'email' => 'other@example.com', 'password' => bcrypt('x')]);
        $other    = Tenant::create(['name' => 'Other', 'slug' => 'other2', 'timezone' => 'UTC', 'currency' => 'USD', 'owner_id' => $user2->id]);
        \DB::table('users')->where('id', $user2->id)->update(['tenant_id' => $other->id]);
        $provider = Provider::create(['tenant_id' => $other->id, 'user_id' => $user2->id]);
        $this->actingAs($this->user);

        Livewire::test(FormBuilder::class)
            ->call('openProviderEdit', $provider->id)
            ->assertSet('showProviderModal', false);
    }
}
