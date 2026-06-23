<?php

namespace App\Filament\Tenant\Resources\BookingResource\Pages;

use App\Filament\Tenant\Resources\BookingResource;
use App\Models\SlotReservation;
use App\Support\TenantContext;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    public function mount(): void
    {
        parent::mount();

        // Support ?client=email@example.com from the Clients page "View Bookings" action.
        // Switch to "all" tab and pre-fill search so results aren't filtered by date.
        $clientEmail = request()->query('client');
        if ($clientEmail) {
            $this->activeTab  = 'all';
            $this->tableSearch = $clientEmail;
        }

        $this->tableSort = 'date:asc';
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->cachedDefaultTableColumnState = null;
        $this->applyTableColumnManager();

        $this->tableSort = match ($this->activeTab) {
            'past'  => 'date:desc',
            default => 'date:asc',
        };
    }

    protected function getHeaderActions(): array
    {
        $tenant         = TenantContext::current();
        $bookingPageUrl = $tenant?->slug ? url('/' . $tenant->slug) : null;

        $actions = [];

        if ($bookingPageUrl) {
            $actions[] = Action::make('open_booking_page')
                ->label(__('Booking Page'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url($bookingPageUrl, shouldOpenInNewTab: true);
        }

        $actions[] = Action::make('calendar_view')
            ->label(__('Calendar View'))
            ->icon('heroicon-o-calendar')
            ->color('gray')
            ->url(route('filament.tenant.pages.booking-calendar'));

        return $actions;
    }

    public function getTabs(): array
    {
        $tenantId = TenantContext::id();

        $todayCount = SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        return [
            'upcoming' => Tab::make(__('Upcoming'))
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereDate('date', '>=', today())
                ),
            'past' => Tab::make(__('Past'))
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereDate('date', '<', today())
                ),
            'all' => Tab::make(__('All')),
            'today' => Tab::make(__('Today'))
                ->badge($todayCount ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereDate('date', today())
                ),
        ];
    }
}
