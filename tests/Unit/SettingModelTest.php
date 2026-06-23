<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_null_when_key_missing_and_no_default(): void
    {
        $this->assertNull(Setting::get('nonexistent_key'));
    }

    public function test_get_returns_default_when_key_missing(): void
    {
        $result = Setting::get('nonexistent_key', 'default_value');

        $this->assertSame('default_value', $result);
    }

    public function test_get_returns_stored_value(): void
    {
        Setting::set('site_name', 'My App');

        $result = Setting::get('site_name');

        $this->assertSame('My App', $result);
    }

    public function test_set_creates_new_setting(): void
    {
        Setting::set('my_key', 'my_value');

        $this->assertDatabaseHas('settings', [
            'key'   => 'my_key',
            'value' => 'my_value',
        ]);
    }

    public function test_set_updates_existing_setting(): void
    {
        Setting::set('unique_update_key', 'original');
        Setting::set('unique_update_key', 'updated');

        $this->assertDatabaseHas('settings', [
            'key'   => 'unique_update_key',
            'value' => 'updated',
        ]);

        $count = \DB::table('settings')->where('key', 'unique_update_key')->count();
        $this->assertSame(1, $count);
    }

    public function test_set_busts_cache(): void
    {
        Cache::put('setting.my_key', 'cached_value', 3600);

        Setting::set('my_key', 'new_value');

        // Cache should be cleared by set(); next get() reads from DB
        $result = Setting::get('my_key');
        $this->assertSame('new_value', $result);
    }

    public function test_get_caches_value(): void
    {
        Setting::set('cached_key', 'cached_value');

        // Trigger cache population
        Setting::get('cached_key');

        // The cache entry should now exist
        $this->assertTrue(Cache::has('setting.cached_key'));
        $this->assertSame('cached_value', Cache::get('setting.cached_key'));
    }

    public function test_saved_event_busts_cache(): void
    {
        $setting = Setting::set('event_key', 'original');

        // Warm the cache
        Setting::get('event_key');
        $this->assertTrue(Cache::has('setting.event_key'));

        // Directly updating the model triggers saved event → cache bust
        $setting->value = 'updated';
        $setting->save();

        $this->assertFalse(Cache::has('setting.event_key'));
    }

    public function test_deleted_event_busts_cache(): void
    {
        $setting = Setting::set('delete_key', 'value');
        Setting::get('delete_key'); // warm cache

        $setting->delete();

        $this->assertFalse(Cache::has('setting.delete_key'));
    }
}
