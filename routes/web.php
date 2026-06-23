<?php

use App\Http\Controllers\FontProxyController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\LocaleSwitchController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\IpRestrictionMiddleware;
use App\Http\Middleware\TrackUserVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

if (! is_dir(base_path('vendor'))) {
    throw new RuntimeException('Missing vendor dependencies. Run "composer install --no-dev --optimize-autoloader" before opening the app.');
}

require __DIR__.'/auth.php';

if (app()->environment('local', 'testing')) {
    require __DIR__.'/dev.php';
}

// Stripe webhook — CSRF exempt (see bootstrap/app.php), raw POST from Stripe
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');
Route::post('/razorpay/webhook', [\App\Http\Controllers\RazorpayWebhookController::class, 'handle'])
    ->name('razorpay.webhook');
Route::post('/paypal/webhook', [\App\Http\Controllers\PayPalWebhookController::class, 'handle'])
    ->name('paypal.webhook');
Route::post('/paddle/webhook', [\App\Http\Controllers\PaddleWebhookController::class, 'handle'])
    ->name('paddle.webhook');

// Font proxy: serves Google Fonts CSS server-side so browsers never touch fonts.googleapis.com
// Cached 24 h; required for CodeCanyon standalone compliance.
Route::get('/font-proxy', FontProxyController::class)->name('font.proxy');

// Installer routes must always be registered so they exist when route cache is used.
// InstallerMiddleware redirects to home when app is already installed (.installed exists).
Route::middleware('web')
    ->group(base_path('routes/installer.php'));

Route::get('/file/{path}', [HomeController::class, 'viewFile'])
    ->name('file.view')
    ->middleware('signed')
    ->where('path', '[A-Za-z0-9+/=]+');

// Cookie consent route
Route::post('/cookie-consent/accept', [CookieConsentController::class, 'accept'])
    ->name('cookie-consent.accept');

// Language switcher — stores locale in session (and DB for authenticated users)
Route::post('/set-locale', LocaleSwitchController::class)->name('locale.switch');

Route::middleware(['web'])->group(function () {
    Route::middleware([TrackUserVisit::class, IpRestrictionMiddleware::class])->group(function () {
        Broadcast::routes(['middleware' => ['web', 'auth']]);
        Route::get('/', [HomeController::class, 'forBusinesses'])->name('home');
        Route::get('/for-businesses', [HomeController::class, 'forBusinesses'])->name('for-businesses');
        Route::get('/for-clients', [HomeController::class, 'index'])->name('for-clients');

        // Contact Us
        Route::get('/contact', [ContactUsController::class, 'index'])->name('contact');
        Route::post('/contact', [ContactUsController::class, 'store'])->name('contact.submit');

        // Static pages (privacy policy, terms, etc.)
        Route::get('/pages/{slug}', [PageController::class, 'show'])->name('page.show');

        Route::middleware(['auth'])->group(function () {
            Route::get('/admin/email-layouts/preview/{layout}', [EmailTemplateController::class, 'previewLayout'])
                ->name('email-layouts.preview');

            Route::get('/admin/email-template/preview/{template}', [EmailTemplateController::class, 'previewTemplate'])
                ->name('email-template.preview');

            // Cross-tenant My Bookings — shows all upcoming bookings for the authenticated user
            Route::get('/my-bookings', [\App\Http\Controllers\MyBookingsController::class, 'index'])
                ->name('my-bookings');
            Route::post('/my-bookings/cancel/{token}', [\App\Http\Controllers\MyBookingsController::class, 'cancel'])
                ->name('my-bookings.cancel');
        });

        // Image resizing route - with validation and rate limiting
        Route::get('/img/{width}/{height}', [ImageController::class, 'resize']);

        // Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth', EnsureSuperAdmin::class]], function () {
        //     // Restrict access to super_admin only
        //     \UniSharp\LaravelFilemanager\Lfm::routes();
        // });

        Route::get('/generate-sitemap', [SitemapController::class, 'generate'])
            ->middleware(['auth', EnsureSuperAdmin::class]);

        // HTML Preview route for form preview
        Route::middleware(['web', 'auth'])->get('admin/content-preview', function (Request $request) {
            $raw = $request->get('html', '');

            $html = '';
            if (!empty($raw)) {
                $decoded = base64_decode($raw, true);
                $html = $decoded !== false ? $decoded : $raw;
            }

            // Sanitize with HTMLPurifier — strips scripts, event handlers, and injection vectors
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'p,b,i,u,em,strong,a[href|title],ul,ol,li,br,span[class|style],div[class|style],h1,h2,h3,h4,h5,h6,img[src|alt|class|style|width|height],table,thead,tbody,tr,th[class|style],td[class|style],blockquote,code,pre,hr,section[class|style],article[class|style],header[class|style],footer[class|style],nav[class|style],aside[class|style],main[class|style]');
            $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,color,background-color,text-align,margin,padding,border,width,height,display,flex,flex-direction,gap,border-radius,line-height,text-decoration,list-style');
            $config->set('AutoFormat.AutoParagraph', false);
            $config->set('Output.TidyFormat', false);

            $purifier = new HTMLPurifier($config);
            $safeHtml = $purifier->purify($html);

            return view('filament.pages.content-preview', ['html' => $safeHtml]);
        })->name('content.preview');

    });
});

// ── Tenant Billing (auth + tenant-scoped) ────────────────────────────────
// Uses Laravel's standard auth (not Filament panel auth) — Filament\Http\Middleware\Authenticate
// runs canAccessPanel() against the default panel (admin), which blocks tenant_owner users with 403.
Route::middleware(['web', 'auth', \App\Http\Middleware\SetTenantFromUser::class])
    ->prefix('manage')
    ->group(function () {
        Route::get('/billing/portal', [\App\Http\Controllers\BillingController::class, 'portal'])
            ->name('billing.portal');
        Route::post('/billing/checkout/{plan:slug}/{cycle}', [\App\Http\Controllers\BillingController::class, 'checkout'])
            ->name('billing.checkout')
            ->middleware('throttle:10,1')
            ->where('cycle', 'monthly|yearly|weekly');

        // Calendar JSON endpoints — consumed by FullCalendar on the booking-calendar page
        Route::get('/booking-calendar/events',      [\App\Http\Controllers\BookingCalendarController::class, 'events'])->name('booking.calendar.events');
        Route::get('/booking-calendar/show/{id}',   [\App\Http\Controllers\BookingCalendarController::class, 'show'])->name('booking.calendar.show')->where('id', '[0-9]+');

        Route::get('/calendar/oauth', [\App\Http\Controllers\GoogleCalendarController::class, 'redirect'])
            ->name('calendar.oauth.redirect');
        Route::get('/calendar/oauth/callback', [\App\Http\Controllers\GoogleCalendarController::class, 'callback'])
            ->name('calendar.oauth.callback');
        Route::post('/calendar/oauth/disconnect', [\App\Http\Controllers\GoogleCalendarController::class, 'disconnect'])
            ->name('calendar.oauth.disconnect');
    });

// ── Tenant Onboarding (auth required, no tenant yet) ──────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/setup', \App\Livewire\Tenant\OnboardingWizard::class)->name('tenant.setup');
});

// ── Client Impersonation ───────────────────────────────────────────────────
Route::middleware(['auth'])->get('/impersonate-client/{user}', function (\App\Models\User $user) {
    // Only super_admin may impersonate clients
    if (! auth()->user()?->hasRole('super_admin')) {
        abort(403);
    }
    // Only allow impersonating client-role users
    if (! $user->hasRole('client')) {
        abort(403, 'Only client accounts can be impersonated via this route.');
    }

    session([
        'impersonate_client_id'    => $user->id,
        'impersonate_client_name'  => $user->name,
        'impersonate_client_email' => $user->email,
        'impersonate_admin_id'     => auth()->id(),
    ]);

    return redirect()->route('my-bookings');
})->name('impersonate.client');

Route::middleware(['auth'])->get('/impersonate-client-exit', function () {
    session()->forget([
        'impersonate_client_id',
        'impersonate_client_name',
        'impersonate_client_email',
        'impersonate_admin_id',
    ]);

    return redirect('/admin/clients');
})->name('impersonate.client.exit');

// ── Impersonation Exit ─────────────────────────────────────────────────────
Route::middleware(['auth'])->get('/impersonate-exit', function () {
    // Log end of impersonation session
    $tenantId = session('impersonate_tenant_id');
    if ($tenantId) {
        \App\Models\ImpersonationLog::withoutGlobalScope('tenant')
            ->where('admin_id', auth()->id())
            ->where('tenant_id', $tenantId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first()
            ?->update(['ended_at' => now()]);
    }

    session()->forget('impersonate_tenant_id');
    return redirect('/admin/tenants');
})->name('impersonate.exit');

// ── Public Booking Pages ───────────────────────────────────────────────────
Route::middleware(['identify.tenant'])
    ->prefix('{tenant}')
    ->group(function () {
        Route::get('/',    \App\Livewire\Booking\BookingWizard::class)->name('booking.index');
        Route::get('/book', \App\Livewire\Booking\BookingWizard::class)->name('booking.wizard');
    });

// iCal download — scoped by cancellation_token (UUID), no sequential ID guessing
Route::get('/booking/{token}/ical', [\App\Http\Controllers\BookingController::class, 'ical'])
    ->name('booking.ical');

// Self-service cancellation via signed URL token (no login required)
Route::get('/booking/cancel/{token}',  [\App\Http\Controllers\BookingController::class, 'cancelShow'])
    ->name('booking.cancel');
Route::post('/booking/cancel/{token}', [\App\Http\Controllers\BookingController::class, 'cancelConfirm'])
    ->name('booking.cancel.confirm');

Route::get('/booking/payment/{token}/success', [\App\Http\Controllers\BookingPaymentController::class, 'success'])
    ->name('booking.payment.success');
Route::get('/booking/payment/{token}/cancel', [\App\Http\Controllers\BookingPaymentController::class, 'cancel'])
    ->name('booking.payment.cancel');
Route::get('/booking/{token}/receipt', [\App\Http\Controllers\BookingReceiptController::class, 'show'])
    ->name('booking.receipt');

