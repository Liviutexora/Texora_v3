<?php

namespace App\Filament\Tenant\Pages;

use App\Models\SubscriptionPlan;
use App\Services\BillingService;
use App\Support\TenantContext;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class BillingPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string                 $navigationLabel = null;
    protected static ?string                 $title           = null;
    protected string                         $view            = 'filament.tenant.pages.billing';
    protected static string|\UnitEnum|null   $navigationGroup = 'Account';
    protected static ?int                    $navigationSort  = 90;
    protected static ?string                 $slug            = 'billing';

    // Exposed to the view
    public string $billingState   = 'free';   // free | checkout_only | active
    public array  $paidPlans      = [];
    public array  $invoices       = [];
    public ?int   $currentPlanId  = null;

    // Plan limit stats exposed to the view
    public int  $providerCount       = 0;
    public ?int $maxProviders        = null;
    public int  $serviceCount        = 0;
    public ?int $maxServices         = null;
    public int  $bookingsThisMonth   = 0;
    public ?int $maxBookingsPerMonth = null;
    public bool $showLimitBars       = false;

    public function mount(): void
    {
        $tenant = TenantContext::current();
        if (! $tenant) {
            return;
        }

        $billing = app(BillingService::class);

        // Handle return from Stripe (bust cache, refresh model, show notification)
        if (request()->query('upgraded') || request()->query('session') === 'done') {
            $billing->bustInvoiceCache($tenant);
            $tenant->refresh();

            Notification::make()
                ->title(__('Subscription updated'))
                ->body(__('Your subscription has been updated successfully.'))
                ->success()
                ->send();
        }

        // Handle errors passed back from BillingController
        if (session()->has('billing_error')) {
            Notification::make()
                ->title(__('Billing error'))
                ->body(session('billing_error'))
                ->warning()
                ->persistent()
                ->send();
            session()->forget('billing_error');
        }

        $this->billingState  = $billing->billingState($tenant);
        $this->currentPlanId = $tenant->plan_id;
        $this->invoices      = $billing->getRecentInvoices($tenant);

        // Paid plans with their active prices for the upgrade CTA
        $this->paidPlans = SubscriptionPlan::where('is_active', true)
            ->with(['activePrices' => fn ($q) => $q->where('price', '>', 0)])
            ->get()
            ->filter(fn ($p) => $p->activePrices->isNotEmpty())
            ->values()
            ->toArray();

        // Plan limit stats — only show if tenant has a plan assigned
        if ($tenant->plan_id) {
            $plan = $tenant->plan;
            $this->showLimitBars       = true;
            $this->maxProviders        = $plan?->max_providers;
            $this->maxServices         = $plan?->max_services;
            $this->maxBookingsPerMonth = $plan?->max_bookings_per_month;
            $this->providerCount       = $tenant->providers()->count();
            $this->serviceCount        = $tenant->services()->count();

            if ($this->maxBookingsPerMonth !== null) {
                $tz = $tenant->timezone ?: 'UTC';
                $startOfMonth = now($tz)->startOfMonth();
                $endOfMonth   = now($tz)->endOfMonth();
                $this->bookingsThisMonth = $tenant->bookings()
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count();
            }
        }
    }

    public function getTitle(): string|Htmlable
    {
        return __('My Subscription');
    }

    /**
     * Only tenant owners see billing. Staff should not see or access this page.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public static function getNavigationLabel(): string
    {
        return __('My Subscription');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
