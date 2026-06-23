<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\SlotReservation;
use App\Support\TenantContext;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = null;

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return __('Upcoming Bookings');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SlotReservation::withoutGlobalScope('tenant')
                    ->where('tenant_id', TenantContext::id())
                    ->whereDate('date', '>=', today())
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->with(['service', 'provider'])
                    ->orderBy('date')
                    ->orderBy('start_time')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date('D, d M')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('Time'))
                    ->time('H:i'),
                TextColumn::make('service.name')
                    ->label(__('Service')),
                TextColumn::make('provider.name')
                    ->label(__('Provider')),
                TextColumn::make('name')
                    ->label(__('Client')),
                TextColumn::make('phone')
                    ->label(__('Phone')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending'   => 'warning',
                        default     => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
