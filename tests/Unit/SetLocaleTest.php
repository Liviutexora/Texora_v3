<?php

namespace Tests\Unit;

use App\Http\Middleware\SetLocale;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TenantContext::clear(); // uses clear() to reset static state between tests
        session()->flush();
    }

    public function test_falls_back_to_app_default_when_nothing_is_set(): void
    {
        config(['app.locale' => 'en']);

        $this->get('/');

        $this->assertEquals('en', App::getLocale());
    }

    public function test_session_locale_is_used_when_set(): void
    {
        $this->withSession(['locale' => 'de'])
            ->get('/');

        $this->assertEquals('de', App::getLocale());
    }

    public function test_unsupported_locale_in_session_falls_back_to_default(): void
    {
        config(['app.locale' => 'en']);

        $this->withSession(['locale' => 'klingon'])
            ->get('/');

        $this->assertEquals('en', App::getLocale());
    }

    public function test_locale_switch_route_stores_locale_in_session(): void
    {
        $this->post(route('locale.switch'), ['locale' => 'fr'])
            ->assertSessionHas('locale', 'fr');
    }

    public function test_locale_switch_ignores_unsupported_locale(): void
    {
        // 'klingon' is unsupported — controller returns early without writing to session
        // The middleware still writes the fallback locale, but NOT 'klingon'
        $response = $this->post(route('locale.switch'), ['locale' => 'klingon']);
        $this->assertNotEquals('klingon', session('locale'));
    }

    public function test_authenticated_user_locale_takes_priority_over_session(): void
    {
        $user = User::factory()->create(['locale' => 'es']);
        $this->actingAs($user)
            ->withSession(['locale' => 'de'])
            ->get('/');

        $this->assertEquals('es', App::getLocale());
    }

    public function test_all_supported_locales_are_accepted(): void
    {
        foreach (SetLocale::SUPPORTED_LOCALES as $locale) {
            $this->post(route('locale.switch'), ['locale' => $locale])
                ->assertSessionHas('locale', $locale);
        }
    }
}
