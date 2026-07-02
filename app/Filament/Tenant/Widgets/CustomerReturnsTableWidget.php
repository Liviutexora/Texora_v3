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
    private const FOLLOWUP_RETRY_DAYS = 3;

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

                TextColumn::make('reminder_badge')
                    ->label(__('Reminder'))
                    ->state(function ($record): string {
                        if (! $record->next_followup_at) {
                            return '—';
                        }

                        $days = today()->diffInDays(Carbon::parse($record->next_followup_at)->startOfDay(), false);

                        if ($days === 0) {
                            return __('Astăzi');
                        }

                        if ($days > 0) {
                            return '−' . $days . ' ' . ($days === 1 ? __('zi') : __('zile'));
                        }

                        $overdue = abs($days);

                        return '+' . $overdue . ' ' . ($overdue === 1 ? __('zi') : __('zile'));
                    })
                    ->badge()
                    ->alignCenter()
                    ->extraAttributes(['class' => 'text-center [&_.fi-badge]:text-base [&_.fi-badge]:px-3.5'])
                    ->color(function ($record): string {
                        if (! $record->next_followup_at) {
                            return 'gray';
                        }

                        $days = today()->diffInDays(Carbon::parse($record->next_followup_at)->startOfDay(), false);

                        if ($days <= 0) {
                            return 'danger';
                        }

                        if ($days === 1) {
                            return 'orange';
                        }

                        if ($days <= 7) {
                            return 'warning';
                        }

                        return 'success';
                    }),

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
                        ->action(fn ($record) => $this->applyFollowupAction(
                            recordId: (int) $record->id,
                            status: 'sms_sent',
                            lastAction: 'sms_sent',
                            historyChannel: 'sms',
                        )),
                    Action::make('send_email')
                        ->label(__('Trimite Email'))
                        ->action(fn ($record) => $this->applyFollowupAction(
                            recordId: (int) $record->id,
                            status: 'email_sent',
                            lastAction: 'email_sent',
                            historyChannel: 'email',
                        )),
                    Action::make('mark_called')
                        ->label(__('Marchează ca apelat'))
                        ->action(fn ($record) => $this->applyFollowupAction(
                            recordId: (int) $record->id,
                            status: 'called',
                            lastAction: 'called',
                            historyChannel: 'phone',
                        )),
                    Action::make('action_divider')
                        ->label('-------------------------')
                        ->disabled()
                        ->color('gray')
                        ->action(static fn () => null),
                    Action::make('booking_completed')
                        ->label(__('Programare realizată'))
                        ->action(fn ($record) => $this->applyFollowupAction(
                            recordId: (int) $record->id,
                            status: 'completed',
                            lastAction: 'booking_completed',
                            historyChannel: 'manual',
                            closeWorkflow: true,
                        )),
                    Action::make('client_inactive')
                        ->label(__('Client inactiv'))
                        ->action(fn ($record) => $this->applyFollowupAction(
                            recordId: (int) $record->id,
                            status: 'inactive',
                            lastAction: 'client_inactive',
                            historyChannel: 'manual',
                            closeWorkflow: true,
                        )),
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
            ->whereNull('customer_followups.completed_at')
            ->join('slot_reservations as sr', 'sr.id', '=', 'customer_followups.slot_reservation_id')
            ->whereNotNull('sr.email')
            ->select([
                'customer_followups.id',
                'customer_followups.service_id',
                'customer_followups.next_followup_at',
                'sr.name as name',
                'sr.email as email',
                'sr.phone as phone',
                'sr.date as last_booking_date',
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

    private function applyFollowupAction(
        int $recordId,
        string $status,
        string $lastAction,
        string $historyChannel,
        bool $closeWorkflow = false,
    ): void {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return;
        }

        $followup = CustomerFollowup::query()
            ->whereKey($recordId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $followup) {
            return;
        }

        $timestamp = now();
        $updateData = [
            'status' => $status,
            'last_action' => $lastAction,
            'last_action_at' => $timestamp,
        ];

        if ($closeWorkflow) {
            $updateData['completed_at'] = $timestamp;
        } else {
            $updateData['next_followup_at'] = $timestamp->copy()->addDays(self::FOLLOWUP_RETRY_DAYS);
        }

        DB::transaction(function () use ($followup, $updateData, $lastAction, $historyChannel, $timestamp): void {
            $followup->update($updateData);

            $followup->history()->create([
                'action' => $lastAction,
                'channel' => $historyChannel,
                'notes' => null,
                'created_by' => auth()->id(),
                'created_at' => $timestamp,
            ]);
        });
    }
}
