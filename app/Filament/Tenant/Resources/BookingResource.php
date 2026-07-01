<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\BookingResource\Pages;
use App\Helpers\NotificationHelper;
use App\Jobs\SendBookingCancellationEmail;
use App\Jobs\SendBookingCancellationSms;
use App\Jobs\SendBookingRescheduledEmail;
use App\Jobs\SendBookingRescheduledSms;
use App\Models\Provider;
use App\Models\SlotReservation;
use App\Services\BookingPaymentService;
use App\Support\TenantContext;
use App\Support\TenantPaymentSettings;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Service;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = SlotReservation::class;

    protected static ?string $modelLabel = 'Booking';

    protected static ?string $pluralModelLabel = 'Bookings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return null;
        }
        $count = SlotReservation::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('date', today())
            ->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make(__('Booking Details'))
                ->columns(2)
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('id')->label(__('Booking #')),
                    \Filament\Infolists\Components\TextEntry::make('status')->label(__('Status'))->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'confirmed' => 'success', 'pending' => 'warning',
                            'cancelled' => 'danger', 'completed' => 'info', default => 'gray',
                        }),
                    \Filament\Infolists\Components\TextEntry::make('date')->label(__('Date'))->date('D, d M Y'),
                    \Filament\Infolists\Components\TextEntry::make('start_time')->label(__('Time'))->time('H:i'),

                    // Service — show all selected services when multi-service booking
                    \Filament\Infolists\Components\TextEntry::make('service_display')
                        ->label(__('Service(s)'))
                        ->columnSpanFull()
                        ->state(function ($record) {
                            $answers = $record->custom_answers ?? [];
                            $extraIds = is_array($answers) ? ($answers['_service_ids'] ?? []) : [];
                            if (count($extraIds) > 1) {
                                $names = \App\Models\Service::withoutGlobalScope('tenant')
                                    ->whereIn('id', $extraIds)->pluck('name');
                                return $names->implode(', ');
                            }
                            return $record->service?->name ?? '—';
                        }),

                    \Filament\Infolists\Components\TextEntry::make('provider.name')->label(__('Provider')),
                    \Filament\Infolists\Components\TextEntry::make('amount')
                        ->label(__('Amount'))
                        ->money(fn ($record) => $record->currency ?? 'INR')
                        ->visible(fn () => ! auth()->user()?->hasRole('staff')),
                    \Filament\Infolists\Components\TextEntry::make('cancellation_reason')
                        ->label(__('Cancellation reason'))
                        ->placeholder(__('—'))
                        ->columnSpanFull()
                        ->visible(fn ($record) => $record->status === 'cancelled' && filled($record->cancellation_reason)),
                ]),

            \Filament\Schemas\Components\Section::make(__('Client Details'))
                ->columns(2)
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('name')->label(__('Client')),
                    \Filament\Infolists\Components\TextEntry::make('email')->label(__('Email'))->copyable(),
                    \Filament\Infolists\Components\TextEntry::make('phone')->label(__('Phone'))->placeholder(__('—')),
                    \Filament\Infolists\Components\TextEntry::make('note')->label(__('Note'))->placeholder(__('—'))->columnSpanFull(),
                ]),

            // Custom field answers — excludes signature, and skips fields that map to
            // the standard infolist entries (name/email/phone/note) already shown above.
            \Filament\Schemas\Components\Section::make(__('Additional Details'))
                ->columnSpanFull()
                ->hidden(fn ($record) => !self::hasCustomTextAnswers($record))
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('custom_field_answers')
                        ->label('')
                        ->columnSpanFull()
                        ->html()
                        ->state(function ($record) {
                    $tenant  = \App\Models\Tenant::find($record->tenant_id);
                    $fields  = $tenant?->custom_fields ?? [];
                    $answers = $record->custom_answers ?? [];
                    if (empty($fields) || empty($answers)) return null;

                    $nameLabels = ['full name', 'name', 'your name'];
                    $noteLabels = ['notes', 'note', 'message', 'special requests'];

                    $html = '<dl style="display:grid;grid-template-columns:max-content 1fr;gap:4px 16px;margin:0">';
                    $hasRow = false;
                    foreach ($fields as $idx => $field) {
                        if ($field['hidden'] ?? false) continue;
                        $type  = $field['type'] ?? 'short_text';
                        $labelLower = strtolower($field['label'] ?? '');

                        // Skip fields already shown as dedicated infolist entries
                        if ($type === 'signature') continue;
                        if ($type === 'email') continue;
                        if ($type === 'phone') continue;
                        if (in_array($labelLower, $nameLabels)) continue;
                        if (in_array($labelLower, $noteLabels)) continue;

                        $val = $answers[$idx] ?? null;
                        if ($val === null || $val === '' || $val === false) continue;

                        $label = e($field['label'] ?? "Field {$idx}");

                        if ($type === 'file_upload' && is_string($val) && $val !== '') {
                            $url      = \Illuminate\Support\Facades\Storage::disk('public')->url($val);
                            $filename = e(basename($val));
                            $display  = "<a href='" . e($url) . "' target='_blank' rel='noopener'"
                                      . " style='color:#6366f1;text-decoration:underline'>{$filename}</a>";
                        } elseif ($type === 'checkbox') {
                            $display = $val ? '✓ Yes' : '✗ No';
                        } else {
                            $display = is_array($val) ? e(implode(', ', $val)) : e((string) $val);
                        }

                        $html .= "<dt style='font-size:12px;color:#6b7280;font-weight:500;padding:3px 0;white-space:nowrap'>{$label}</dt>"
                               . "<dd style='font-size:13px;color:inherit;margin:0;padding:3px 0'>{$display}</dd>";
                        $hasRow = true;
                    }
                    $html .= '</dl>';
                    return $hasRow ? $html : null;
                }),
                ]),

            // Signature images
            \Filament\Schemas\Components\Section::make(__('Signature(s)'))
                ->columnSpanFull()
                ->hidden(fn ($record) => !self::hasSignature($record))
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('signature_fields')
                        ->label('')
                        ->columnSpanFull()
                        ->html()
                        ->state(function ($record) {
                            $tenant  = \App\Models\Tenant::find($record->tenant_id);
                            $fields  = $tenant?->custom_fields ?? [];
                            $answers = $record->custom_answers ?? [];
                            $html = '';
                            foreach ($fields as $idx => $field) {
                                if (($field['type'] ?? '') !== 'signature') continue;
                                $val = $answers[$idx] ?? '';
                                if (!str_starts_with((string) $val, 'data:image')) continue;
                                $label = e($field['label'] ?? 'Signature');
                                $html .= "<div style='margin-bottom:12px'>"
                                       . "<div style='font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px'>{$label}</div>"
                                       . "<div style='border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;background:#fff;max-width:300px'>"
                                       . "<img src='" . e($val) . "' style='width:100%;max-height:100px;object-fit:contain;display:block'>"
                                       . "</div></div>";
                            }
                            return $html ?: null;
                        }),
                ]),
        ]);
    }

    private static function hasSignature($record): bool
    {
        $tenant  = \App\Models\Tenant::find($record->tenant_id);
        $fields  = $tenant?->custom_fields ?? [];
        $answers = $record->custom_answers ?? [];
        foreach ($fields as $idx => $field) {
            if (($field['type'] ?? '') === 'signature' && str_starts_with((string) ($answers[$idx] ?? ''), 'data:image')) {
                return true;
            }
        }
        return false;
    }

    private static function hasCustomTextAnswers($record): bool
    {
        $tenant     = \App\Models\Tenant::find($record->tenant_id);
        $fields     = $tenant?->custom_fields ?? [];
        $answers    = $record->custom_answers ?? [];
        $nameLabels = ['full name', 'name', 'your name'];
        $noteLabels = ['notes', 'note', 'message', 'special requests'];
        foreach ($fields as $idx => $field) {
            if ($field['hidden'] ?? false) continue;
            $type = $field['type'] ?? 'short_text';
            if ($type === 'signature') continue;
            if ($type === 'email') continue;
            if ($type === 'phone') continue;
            if (in_array(strtolower($field['label'] ?? ''), $nameLabels)) continue;
            if (in_array(strtolower($field['label'] ?? ''), $noteLabels)) continue;
            $val = $answers[$idx] ?? null;
            if ($val !== null && $val !== '' && $val !== false) return true;
        }
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('#'))->sortable(),
                TextColumn::make('service.name')->label(__('Service'))->searchable()->sortable(),
                TextColumn::make('provider.name')->label(__('Provider'))->searchable(),
                TextColumn::make('name')->label(__('Client'))->searchable(),
                TextColumn::make('email')->label(__('Email'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')->label(__('Phone'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date')->label(__('Date'))->date('d M Y')->sortable(),
                TextColumn::make('start_time')->label(__('Time'))->time('H:i'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed'  => 'success',
                        'pending'    => 'warning',
                        'cancelled'  => 'danger',
                        'completed'  => 'info',
                        'no_show'    => 'gray',
                        default      => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->label(__('Payment'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'     => 'success',
                        'pending'  => 'warning',
                        'refunded' => 'info',
                        default    => 'gray',
                    })
                    ->visible(fn () => ! auth()->user()?->hasRole('staff')),
                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money(fn ($record) => $record->currency ?? 'INR')
                    ->visible(fn () => ! auth()->user()?->hasRole('staff')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'   => __('Pending'),
                        'confirmed' => __('Confirmed'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                        'no_show'   => __('No Show'),
                    ]),
                SelectFilter::make('service_id')
                    ->label(__('Service'))
                    ->relationship('service', 'name'),
                SelectFilter::make('provider_id')
                    ->label(__('Provider'))
                    ->options(function () {
                        $tenantId = TenantContext::id();
                        return \App\Models\User::whereHas('provider', fn ($q) => $q->where('tenant_id', $tenantId))
                            ->pluck('name', 'id');
                    }),
                Filter::make('date_range')
                    ->label(__('Date range'))
                    ->form([
                        DatePicker::make('from')->label(__('From')),
                        DatePicker::make('until')->label(__('Until')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'],  fn ($q) => $q->whereDate('date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'])  $indicators[] = 'From: ' . $data['from'];
                        if ($data['until']) $indicators[] = 'Until: ' . $data['until'];
                        return $indicators;
                    }),
            ])
            ->actions([
                Action::make('reschedule')
                    ->label(__('Reschedule'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->modalWidth('2xl')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                    ->form(function ($record) {
                        $bookingDate    = Carbon::parse($record->date);
                        $timeRange      = substr($record->start_time ?? '', 0, 5)
                            . ($record->end_time ? ' – ' . substr($record->end_time, 0, 5) : '');
                        $bookingId      = (int) $record->id;
                        $providerUserId = (int) $record->provider_id;

                        return [
                            Section::make(__('Current Booking'))
                                ->schema([
                                    Placeholder::make('_cur_date')
                                        ->label(__('Date'))
                                        ->content($bookingDate->format('D, d M Y')),
                                    Placeholder::make('_cur_time')
                                        ->label(__('Time'))
                                        ->content($timeRange ?: '—'),
                                    Placeholder::make('_cur_service')
                                        ->label(__('Service'))
                                        ->content($record->service?->name ?? '—'),
                                    Placeholder::make('_cur_provider')
                                        ->label(__('Provider'))
                                        ->content($record->provider?->name ?? '—'),
                                ])
                                ->columns(2),

                            Section::make(__('Reschedule To'))
                                ->schema([
                                    DatePicker::make('new_date')
                                        ->label(__('New Date'))
                                        ->required()
                                        ->minDate(today())
                                        ->maxDate(now()->addMonths(3))
                                        ->live()
                                        ->afterStateUpdated(fn (Set $set) => $set('new_slot', null))
                                        ->columnSpanFull(),

                                    Radio::make('new_slot')
                                        ->label(__('Available Time Slots'))
                                        ->required()
                                        ->options(function (Get $get) use ($bookingId, $providerUserId) {
                                            $date = $get('new_date');

                                            if (! $date) {
                                                return [];
                                            }

                                            $provider = Provider::withoutGlobalScope('tenant')
                                                ->with([
                                                    'shifts'        => fn ($q) => $q->withoutGlobalScope('tenant'),
                                                    'slotOverrides' => fn ($q) => $q->withoutGlobalScope('tenant'),
                                                ])
                                                ->where('user_id', $providerUserId)
                                                ->first();

                                            if (! $provider) {
                                                return [];
                                            }

                                            $slots  = $provider->getSlotsForDate(Carbon::parse($date));
                                            $booked = SlotReservation::withoutGlobalScope('tenant')
                                                ->where('provider_id', $providerUserId)
                                                ->whereDate('date', $date)
                                                ->whereIn('status', ['pending', 'confirmed'])
                                                ->where('id', '!=', $bookingId)
                                                ->pluck('start_time')
                                                ->map(fn ($t) => substr($t, 0, 5))
                                                ->toArray();

                                            return $slots
                                                ->filter(fn ($s) => $s['status'] === 'available'
                                                    && ! in_array($s['start'], $booked, true))
                                                ->mapWithKeys(fn ($s) => [
                                                    $s['start'] . '|' . $s['end'] => $s['start'] . ' – ' . $s['end'],
                                                ])
                                                ->toArray();
                                        })
                                        ->visible(fn (Get $get) => filled($get('new_date')))
                                        ->columns(4)
                                        ->columnSpanFull(),
                                ]),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        [$newStart, $newEnd] = explode('|', $data['new_slot']);

                        app(BookingPaymentService::class)->maybeDeleteCalendar($record);

                        $record->update([
                            'status'              => 'cancelled',
                            'cancelled_at'        => now(),
                            'cancelled_by'        => auth()->id(),
                            'cancellation_reason' => 'Rescheduled',
                        ]);

                        $newBooking = SlotReservation::withoutGlobalScope('tenant')->create([
                            'tenant_id'      => $record->tenant_id,
                            'service_id'     => $record->service_id,
                            'provider_id'    => $record->provider_id,
                            'date'           => $data['new_date'],
                            'start_time'     => $newStart,
                            'end_time'       => $newEnd,
                            'name'           => $record->name,
                            'email'          => $record->email,
                            'phone'          => $record->phone,
                            'note'           => $record->note,
                            'status'         => 'confirmed',
                            'amount'         => $record->amount,
                            'currency'       => $record->currency,
                            'payment_status' => $record->payment_status,
                            'custom_answers' => $record->custom_answers,
                            'is_verified'    => true,
                        ]);

                        SendBookingRescheduledEmail::dispatch($newBooking)->afterCommit();
                        SendBookingRescheduledSms::dispatch($newBooking)->afterCommit();
                        app(BookingPaymentService::class)->maybeSyncCalendar($newBooking);

                        \Filament\Notifications\Notification::make()
                            ->title(__('Booking rescheduled'))
                            ->success()
                            ->send();
                    }),

                ViewAction::make()->label(__('View')),
                Action::make('update_status')
                    ->label(__('Status'))
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('gray')
                    ->form([
                        Select::make('status')
                            ->label(__('Booking Status'))
                            ->options([
                                'pending'   => __('Pending'),
                                'confirmed' => __('Confirmed'),
                                'completed' => __('Completed'),
                                'cancelled' => __('Cancelled'),
                                'no_show'   => __('No Show'),
                            ])
                            ->required()
                            ->live(),

                        Section::make(__('Customer Follow-up'))
                            ->schema([
                                Checkbox::make('create_followup_reminder')
                                    ->label(__('Create follow-up reminder'))
                                    ->default(false)
                                    ->live()
                                    ->dehydrated(false),

                                Radio::make('reminder_source')
                                    ->label(__('Reminder source'))
                                    ->options([
                                        'service_recommendation' => __('Use service recommendation'),
                                        'custom' => __('Custom reminder'),
                                    ])
                                    ->default('service_recommendation')
                                    ->live()
                                    ->dehydrated(false)
                                    ->visible(fn (Get $get): bool => (bool) $get('create_followup_reminder')),

                                Placeholder::make('recommended_followup')
                                    ->label(__('Recommended follow-up'))
                                    ->content(__('30 days (service setting)'))
                                    ->dehydrated(false)
                                    ->visible(fn (Get $get): bool =>
                                        (bool) $get('create_followup_reminder')
                                        && $get('reminder_source') === 'service_recommendation'
                                    ),

                                TextInput::make('followup_after_days')
                                    ->label(__('Follow-up after (days)'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(365)
                                    ->default(30)
                                    ->dehydrated(false)
                                    ->visible(fn (Get $get): bool =>
                                        (bool) $get('create_followup_reminder')
                                        && $get('reminder_source') === 'custom'
                                    ),

                                CheckboxList::make('reminder_channel')
                                    ->label(__('Reminder channel'))
                                    ->options([
                                        'sms' => __('SMS'),
                                        'email' => __('Email'),
                                    ])
                                    ->default(['sms', 'email'])
                                    ->columns(2)
                                    ->dehydrated(false)
                                    ->visible(fn (Get $get): bool => (bool) $get('create_followup_reminder')),
                            ])
                            ->columns(1)
                            ->visible(fn (Get $get): bool => $get('status') === 'completed'),
                    ])
                    ->fillForm(fn ($record) => ['status' => $record->status])
                    ->action(function ($record, array $data) {
                        $wasNotCancelled = $record->status !== 'cancelled';
                        $record->update(['status' => $data['status']]);

                        if ($data['status'] === 'cancelled' && $wasNotCancelled) {
                            SendBookingCancellationEmail::dispatch($record)->afterCommit();
                            SendBookingCancellationSms::dispatch($record)->afterCommit();
                            app(BookingPaymentService::class)->maybeDeleteCalendar($record->fresh());

                            try {
                                $url = rescue(fn () => route('filament.tenant.resources.bookings.view', ['record' => $record->id]), null);
                                NotificationHelper::sendToTenantWebUsers(
                                    'booking_cancelled',
                                    $record->tenant_id,
                                    'Booking Cancelled',
                                    "Booking #{$record->id} for {$record->service?->name} cancelled",
                                    $url,
                                );
                            } catch (\Throwable) {}

                            try {
                                $e = fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
                                $dashboardUrl = rescue(fn () => route('filament.manage.pages.dashboard'), '');
                                NotificationHelper::sendEmailToTenantUsers(
                                    event: 'booking_cancelled',
                                    tenantId: $record->tenant_id,
                                    subjectFallback: "Booking Cancelled — {$record->service?->name} by {$record->name}",
                                    bodyFallback: <<<HTML
<p>Hi,</p>
<p>A booking has been <strong>cancelled</strong>.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Client</td><td>{{CLIENT_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
</table>
<p><a href="{{DASHBOARD_URL}}" style="display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">View in Dashboard</a></p>
HTML,
                                    placeholders: [
                                        'BOOKING_ID'    => '#' . $record->id,
                                        'CLIENT_NAME'   => $e($record->name ?? ''),
                                        'SERVICE_NAME'  => $record->service?->name ?? '',
                                        'BOOKING_DATE'  => $record->date?->format('D, d M Y') ?? '',
                                        'BOOKING_TIME'  => substr($record->start_time ?? '', 0, 5),
                                        'DASHBOARD_URL' => $dashboardUrl,
                                    ],
                                );
                            } catch (\Throwable) {}
                        }

                        \Filament\Notifications\Notification::make()->title(__('Booking status updated'))->success()->send();
                    }),
                Action::make('update_payment')
                    ->label(__('Payment'))
                    ->icon('heroicon-o-credit-card')
                    ->color('gray')
                    ->visible(fn () => ! auth()->user()?->hasRole('staff'))
                    ->form([
                        Select::make('payment_status')
                            ->label(__('Payment Status'))
                            ->options([
                                'pending'  => __('Pending'),
                                'paid'     => __('Paid'),
                                'refunded' => __('Refunded'),
                                'waived'   => __('Waived'),
                            ])
                            ->required()
                            ->live(),
                        Select::make('offline_method')
                            ->label(__('Payment method'))
                            ->options(function (?SlotReservation $record) {
                                $tenantId = $record?->tenant_id ?? TenantContext::id();
                                if (! $tenantId) {
                                    return ['manual' => __('Manual / other')];
                                }
                                $settings = TenantPaymentSettings::for($tenantId);
                                $options = ['manual' => __('Manual / other')];
                                if ($settings->offlineCashEnabled()) {
                                    $options['cash'] = __('Cash');
                                }
                                if ($settings->offlineCardEnabled()) {
                                    $options['card_terminal'] = __('Card terminal');
                                }
                                if ($settings->offlineBankTransferEnabled()) {
                                    $options['bank_transfer'] = __('Bank transfer');
                                }

                                return $options;
                            })
                            ->visible(fn (Get $get) => $get('payment_status') === 'paid')
                            ->required(fn (Get $get) => $get('payment_status') === 'paid'),
                        TextInput::make('payment_reference')
                            ->label(__('Reference / receipt #'))
                            ->maxLength(255)
                            ->visible(fn (Get $get) => $get('payment_status') === 'paid'),
                        Placeholder::make('receipt_link')
                            ->label(__('Receipt'))
                            ->content(function ($record) {
                                if (! $record?->cancellation_token) {
                                    return '—';
                                }

                                $url = route('booking.receipt', ['token' => $record->cancellation_token]);

                                return new HtmlString(
                                    '<a href="' . e($url) . '" target="_blank" rel="noopener" class="text-primary-600 underline">' . e(__('Open printable receipt')) . '</a>'
                                );
                            }),
                    ])
                    ->fillForm(fn ($record) => [
                        'payment_status' => $record->payment_status,
                        'offline_method' => $record->payment_gateway ?: 'manual',
                        'payment_reference' => $record->payment_reference,
                    ])
                    ->action(function ($record, array $data) {
                        if ($data['payment_status'] === 'paid' && $record->payment_status !== 'paid') {
                            app(BookingPaymentService::class)->recordOfflinePayment(
                                $record,
                                $data['offline_method'] ?? 'manual',
                                $data['payment_reference'] ?? null,
                            );
                        } else {
                            $record->update([
                                'payment_status' => $data['payment_status'],
                                'payment_reference' => $data['payment_reference'] ?? $record->payment_reference,
                            ]);
                        }

                        Notification::make()->title(__('Payment status updated'))->success()->send();
                    }),

                // ── Add as Client ────────────────────────────────────────────
                // Visible only to tenant_owner (not staff).
                // If the booking email already has a registered User → show
                // a disabled "Client Exists" badge.
                // Otherwise → show "Add as Client" and let the owner create
                // a client account on the spot.
                Action::make('add_as_client')
                    ->label(fn ($record) =>
                        User::where('email', $record->email)->exists()
                            ? __('Client ✓')
                            : __('Add as Client')
                    )
                    ->icon(fn ($record) =>
                        User::where('email', $record->email)->exists()
                            ? 'heroicon-o-check-circle'
                            : 'heroicon-o-user-plus'
                    )
                    ->color(fn ($record) =>
                        User::where('email', $record->email)->exists() ? 'success' : 'primary'
                    )
                    ->tooltip(fn ($record) =>
                        User::where('email', $record->email)->exists()
                            ? __('This person already has a client account')
                            : __('Create a client account for :name', ['name' => $record->name ?? $record->email])
                    )
                    ->visible(fn ($record) =>
                        filled($record->email) && ! auth()->user()?->hasRole('staff')
                    )
                    ->disabled(fn ($record) => User::where('email', $record->email)->exists())
                    ->requiresConfirmation(fn ($record) =>
                        ! User::where('email', $record->email)->exists()
                    )
                    ->modalHeading(fn ($record) =>
                        __('Create client account for :name', ['name' => $record->name ?? $record->email])
                    )
                    ->modalDescription(fn ($record) =>
                        __('A login account will be created with the email :email. The client will receive a random password and can reset it via "Forgot password".', ['email' => $record->email])
                    )
                    ->modalSubmitActionLabel(__('Create Account'))
                    ->action(function ($record) {
                        if (User::where('email', $record->email)->exists()) {
                            return;
                        }

                        $user = User::create([
                            'name'              => $record->name ?? 'Client',
                            'email'             => $record->email,
                            'phone_number'      => $record->phone,
                            'password'          => Hash::make(Str::random(20)),
                            'email_verified_at' => now(),
                            'is_active'         => true,
                        ]);

                        $user->assignRole('client');

                        // Link this booking (and any other unlinked bookings
                        // with the same email) to the new user account.
                        SlotReservation::withoutGlobalScope('tenant')
                            ->where('email', $record->email)
                            ->whereNull('user_id')
                            ->update(['user_id' => $user->id]);

                        Notification::make()
                            ->title(__('Client account created'))
                            ->body($user->name . ' (' . $user->email . ') is now a registered client.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('bulk_update_status')
                        ->label(__('Update Status'))
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Select::make('status')
                                ->label(__('Booking Status'))
                                ->options([
                                    'pending'   => __('Pending'),
                                    'confirmed' => __('Confirmed'),
                                    'completed' => __('Completed'),
                                    'cancelled' => __('Cancelled'),
                                    'no_show'   => __('No Show'),
                                ])
                                ->required(),
                        ])
                        ->action(function (EloquentCollection $records, array $data) {
                            $records->each->update(['status' => $data['status']]);
                            \Filament\Notifications\Notification::make()->title(__('Status updated for :count bookings', ['count' => $records->count()]))->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_update_payment')
                        ->label(__('Update Payment'))
                        ->icon('heroicon-o-credit-card')
                        ->visible(fn () => ! auth()->user()?->hasRole('staff'))
                        ->form([
                            Select::make('payment_status')
                                ->label(__('Payment Status'))
                                ->options([
                                    'pending'  => __('Pending'),
                                    'paid'     => __('Paid'),
                                    'refunded' => __('Refunded'),
                                    'waived'   => __('Waived'),
                                ])
                                ->required(),
                        ])
                        ->action(function (EloquentCollection $records, array $data) {
                            $records->each->update(['payment_status' => $data['payment_status']]);
                            \Filament\Notifications\Notification::make()->title(__('Payment updated for :count bookings', ['count' => $records->count()]))->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('export_csv')
                        ->label(__('Export CSV'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (EloquentCollection $records): StreamedResponse {
                            $isStaff = auth()->user()?->hasRole('staff');
                            $headers = [
                                'Content-Type'        => 'text/csv',
                                'Content-Disposition' => 'attachment; filename="bookings-' . now()->format('Y-m-d') . '.csv"',
                            ];

                            $callback = function () use ($records, $isStaff) {
                                $fh = fopen('php://output', 'w');
                                $cols = ['#', 'Service', 'Provider', 'Client', 'Email', 'Phone', 'Date', 'Time', 'Status', 'Notes'];
                                if (! $isStaff) { array_splice($cols, 8, 0, ['Payment', 'Amount', 'Currency']); }
                                fputcsv($fh, $cols);

                                foreach ($records as $r) {
                                    $row = [
                                        $r->id,
                                        $r->service?->name,
                                        optional($r->providerRelation)->user?->name ?? '',
                                        $r->name,
                                        $r->email,
                                        $r->phone,
                                        $r->date?->format('Y-m-d'),
                                        substr($r->start_time ?? '', 0, 5),
                                        $r->status,
                                        $r->note,
                                    ];
                                    if (! $isStaff) { array_splice($row, 8, 0, [$r->payment_status, $r->amount, $r->currency]); }
                                    fputcsv($fh, $row);
                                }

                                fclose($fh);
                            };

                            return response()->stream($callback, 200, $headers);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                // Export all filtered bookings (not just selected)
                Action::make('export_all_csv')
                    ->label(__('Export All (CSV)'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Table $table): StreamedResponse {
                        $isStaff = auth()->user()?->hasRole('staff');
                        $records = static::getEloquentQuery()
                            ->with(['service', 'providerRelation.user'])
                            ->get();

                        $headers = [
                            'Content-Type'        => 'text/csv',
                            'Content-Disposition' => 'attachment; filename="bookings-all-' . now()->format('Y-m-d') . '.csv"',
                        ];

                        $callback = function () use ($records, $isStaff) {
                            $fh = fopen('php://output', 'w');
                            $cols = ['#', 'Service', 'Provider', 'Client', 'Email', 'Phone', 'Date', 'Time', 'Status', 'Notes'];
                            if (! $isStaff) { array_splice($cols, 8, 0, ['Payment', 'Amount', 'Currency']); }
                            fputcsv($fh, $cols);

                            foreach ($records as $r) {
                                $row = [
                                    $r->id,
                                    $r->service?->name,
                                    $r->providerRelation?->user?->name ?? '',
                                    $r->name,
                                    $r->email,
                                    $r->phone,
                                    $r->date?->format('Y-m-d'),
                                    substr($r->start_time ?? '', 0, 5),
                                    $r->status,
                                    $r->note,
                                ];
                                if (! $isStaff) { array_splice($row, 8, 0, [$r->payment_status, $r->amount, $r->currency]); }
                                fputcsv($fh, $row);
                            }

                            fclose($fh);
                        };

                        return response()->stream($callback, 200, $headers);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'view'  => Pages\ViewBooking::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool { return true; }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function getNavigationGroup(): ?string
    {
        return __('Operations');
    }

    public static function getModelLabel(): string
    {
        return __('Booking');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bookings');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function canCreate(): bool { return true; }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return parent::getEloquentQuery()->whereRaw('0 = 1');
        }

        return parent::getEloquentQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->with(['service', 'provider']);
    }
}
