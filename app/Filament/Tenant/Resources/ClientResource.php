<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ClientResource\Pages;
use App\Models\SlotReservation;
use App\Support\TenantContext;
use App\Filament\Tenant\Resources\BookingResource;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = SlotReservation::class;

    public static function getModelLabel(): string
    {
        return __('Client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Clients');
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return null;
        }
        $count = SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('email')
            ->distinct('email')
            ->count('email');
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getEloquentQuery())
            ->columns([
                TextColumn::make('name')
                    ->label(__('Client'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),

                TextColumn::make('total_bookings')
                    ->label(__('Total Bookings'))
                    ->sortable(),

                TextColumn::make('last_booking_date')
                    ->label(__('Last Visit'))
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('favourite_service')
                    ->label(__('Top Service')),

                TextColumn::make('total_spent')
                    ->label(__('Total Spent'))
                    ->money(TenantContext::current()?->currency ?? 'INR')
                    ->sortable(),
            ])
            ->defaultSort('total_bookings', 'desc')
            ->filters([
                SelectFilter::make('service_id')
                    ->label(__('Has booked'))
                    ->relationship('service', 'name'),
            ])
            ->actions([
                Action::make('view_bookings')
                    ->label(__('Bookings'))
                    ->icon('heroicon-o-calendar-days')
                    ->url(fn ($record) => BookingResource::getUrl('index') . '?client=' . urlencode($record->email)),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return parent::getEloquentQuery()->whereRaw('0 = 1');
        }

        // Build the inner aggregated query using the query builder (not Eloquent)
        // so we can use it as a clean subquery.
        $inner = \Illuminate\Support\Facades\DB::table('slot_reservations')
            ->where('slot_reservations.tenant_id', $tenantId)
            ->whereNotNull('slot_reservations.email')
            ->selectRaw('
                MIN(id) as id,
                name,
                email,
                phone,
                COUNT(*) as total_bookings,
                MAX(date) as last_booking_date,
                SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END) as total_spent,
                (
                    SELECT s.name
                    FROM slot_reservations sr2
                    JOIN services s ON sr2.service_id = s.id
                    WHERE sr2.email = slot_reservations.email
                      AND sr2.tenant_id = slot_reservations.tenant_id
                    GROUP BY sr2.service_id
                    ORDER BY COUNT(*) DESC
                    LIMIT 1
                ) as favourite_service
            ')
            ->groupBy('email', 'name', 'phone', 'tenant_id');

        // Wrap in an Eloquent builder via fromSub so Filament gets a proper
        // Builder instance. The outer table alias matches the model's table name
        // so Filament's automatic secondary sort (ORDER BY slot_reservations.id)
        // resolves to the derived table's MIN(id) column — valid under ONLY_FULL_GROUP_BY.
        return SlotReservation::withoutGlobalScope('tenant')
            ->fromSub($inner, 'slot_reservations')
            ->select('slot_reservations.*');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
        ];
    }

    public static function canViewAny(): bool { return auth()->check() && ! auth()->user()->hasRole('staff'); }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    // Clients are read-only — they come from bookings, not created directly
    public static function getNavigationGroup(): ?string
    {
        return __('Operations');
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
