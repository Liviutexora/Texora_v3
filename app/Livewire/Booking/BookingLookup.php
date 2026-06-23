<?php

namespace App\Livewire\Booking;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Illuminate\Support\Collection;
use Livewire\Component;

class BookingLookup extends Component
{
    public string $email    = '';
    public bool   $searched = false;
    public Collection $bookings;

    public function boot(): void
    {
        $this->bookings = collect();
    }

    public function mount(): void
    {
        // When an authenticated user lands here (e.g. demo client account),
        // pre-fill their email and run the search automatically.
        if (auth()->check() && ! auth()->user()->hasAnyRole(['super_admin', 'tenant_owner'])) {
            $this->email = auth()->user()->email;
            $this->search();
        }
    }

    public function search(): void
    {
        $this->validate(['email' => 'required|email']);

        $query = SlotReservation::withoutGlobalScope('tenant')
            ->where('email', $this->email)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('date', '>=', now()->toDateString())
            ->with(['service', 'tenant'])
            ->orderBy('date')
            ->orderBy('start_time');

        // If a tenant context is set (accessed from a tenant's booking page),
        // scope to that tenant. Otherwise show bookings across all tenants
        // so a client can see all their upcoming appointments in one place.
        if (TenantContext::id()) {
            $query->where('tenant_id', TenantContext::id());
        }

        $this->bookings  = $query->get();
        $this->searched  = true;
    }

    public function render()
    {
        $tenant = TenantContext::current();

        return view('livewire.booking.booking-lookup', [
            'tenant' => $tenant,
        ])->layout('layouts.booking', ['tenant' => $tenant]);
    }
}
