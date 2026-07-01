<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\CustomerFollowup;
use App\Support\TenantContext;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerReturnsTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = null;

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return __('Clienți programați pentru reminder');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFollowupQuery())
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
                    ->label(__('Reminder'))
                    ->sortable(),

                TextColumn::make('last_booking_date')
                    ->label(__('Last Visit'))
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('favourite_service')
                    ->label(__('Serviciu')),

                TextColumn::make('total_spent')
                    ->label(__('Status'))
                    ->state(fn ($record): string => (string) ($record->followup_status_label ?? __('Necontactat')))
                    ->description(function ($record): ?string {
                        if (! $record->followup_status_at) {
                            return null;
                        }

                        return Carbon::parse($record->followup_status_at)->format('d.m.Y H:i');
                    })
                    ->sortable(),
            ])
            ->defaultSort('total_bookings', 'desc')
            ->filters([
                SelectFilter::make('service_id')
                    ->label(__('Has booked'))
                    ->relationship('service', 'name'),
            ])
            ->actionsColumnLabel(__('Acțiuni'))
            ->actions([
                ActionGroup::make([
                    Action::make('send_sms')
                        ->label(__('Trimite SMS'))
                        ->action(static fn () => null),
                    Action::make('send_email')
                        ->label(__('Trimite e-mail'))
                        ->action(static fn () => null),
                    Action::make('mark_called')
                        ->label(__('Marchează ca apelat'))
                        ->action(static fn () => null),
                    Action::make('reschedule')
                        ->label(__('Reprogramează'))
                        ->action(static fn () => null),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->iconButton(),
            ]);
    }

    private function getFollowupQuery(): Builder
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return CustomerFollowup::query()->whereRaw('0 = 1');
        }

        return CustomerFollowup::query()
            ->where('customer_followups.tenant_id', $tenantId)
            ->join('slot_reservations as sr', 'sr.id', '=', 'customer_followups.slot_reservation_id')
            ->whereNotNull('sr.email')
            ->select([
                'customer_followups.id',
                'customer_followups.service_id',
                'sr.name as name',
                'sr.email as email',
                'sr.phone as phone',
                DB::raw("CASE customer_followups.status
                    WHEN 'called' THEN 'Apelat'
                    WHEN 'email_sent' THEN 'E-mail trimis'
                    WHEN 'sms_sent' THEN 'SMS trimis'
                    ELSE 'Necontactat'
                END as followup_status_label"),
                DB::raw('COALESCE(customer_followups.last_action_at, customer_followups.scheduled_at) as followup_status_at'),
                DB::raw('0 as total_spent'),
            ])
            ->selectSub(
                function (QueryBuilder $query): void {
                    $query->from('customer_followups as cf2')
                        ->join('slot_reservations as sr2', 'sr2.id', '=', 'cf2.slot_reservation_id')
                        ->whereColumn('cf2.tenant_id', 'customer_followups.tenant_id')
                        ->whereColumn('sr2.email', 'sr.email')
                        ->selectRaw('COUNT(*)');
                },
                'total_bookings'
            )
            ->selectSub(
                function (QueryBuilder $query): void {
                    $query->from('slot_reservations as sr3')
                        ->join('customer_followups as cf3', 'cf3.slot_reservation_id', '=', 'sr3.id')
                        ->whereColumn('cf3.tenant_id', 'customer_followups.tenant_id')
                        ->whereColumn('sr3.email', 'sr.email')
                        ->selectRaw('MAX(sr3.date)');
                },
                'last_booking_date'
            )
            ->selectSub(
                function (QueryBuilder $query): void {
                    $query->from('customer_followups as cf4')
                        ->join('slot_reservations as sr4', 'sr4.id', '=', 'cf4.slot_reservation_id')
                        ->join('services as s4', 's4.id', '=', 'cf4.service_id')
                        ->whereColumn('cf4.tenant_id', 'customer_followups.tenant_id')
                        ->whereColumn('sr4.email', 'sr.email')
                        ->groupBy('cf4.service_id', 's4.name')
                        ->orderByRaw('COUNT(*) DESC')
                        ->limit(1)
                        ->select('s4.name');
                },
                'favourite_service'
            );
    }
}
