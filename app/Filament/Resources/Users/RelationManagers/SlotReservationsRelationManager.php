<?php

namespace App\Filament\Resources\Users\RelationManagers;

use BackedEnum;
use App\Enums\SlotOverrideStatus;
use App\Models\Provider;
use App\Models\ProviderSlotOverride;
use App\Models\SlotReservation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SlotReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'slotReservations';

    protected static ?string $title = 'Appointments';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedCalendarDays;

    protected static IconPosition $iconPosition = IconPosition::Before;

    protected static function formatTime($state): string
    {
        if (! $state) {
            return '—';
        }

        return \is_string($state) ? \Carbon\Carbon::parse($state)->format('g:i A') : $state->format('g:i A');
    }

    protected static function getSlotsHelperText(Get $get): ?string
    {
        if (empty($get('provider_id'))) {
            return null;
        }
        if (empty($get('date'))) {
            return 'Select a date on the calendar first.';
        }
        $provider = Provider::where('user_id', $get('provider_id'))->first();
        if (! $provider) {
            return null;
        }
        $dateCarbon = Carbon::parse($get('date'));
        $dayName = $dateCarbon->format('l');
        $slots = $provider->getSlotsForDate($dateCarbon);
        if ($slots->isEmpty()) {
            return "Provider has no shifts on {$dayName}.";
        }
        $available = $slots->filter(fn ($s) => $s['status'] === 'available')->count();
        if ($available === 0) {
            return 'All slots are booked or blocked for this date.';
        }

        return "{$available} slot(s) available — choose one.";
    }

    protected static function getAvailableSlots($providerUserId, $date, ?string $search = null): array
    {
        if (empty($providerUserId) || empty($date)) {
            return [];
        }

        $provider = Provider::where('user_id', $providerUserId)->first();
        if (! $provider) {
            return [];
        }

        $dateCarbon = Carbon::parse($date);
        $slots = $provider->getSlotsForDate($dateCarbon);

        $availableSlots = $slots
            ->filter(fn ($slot) => $slot['status'] === 'available')
            ->mapWithKeys(fn ($slot) => [
                $slot['start'] . '-' . $slot['end'] => $slot['start'] . ' – ' . $slot['end'] . ' (' . $slot['shift_name'] . ')',
            ]);

        if ($search) {
            $availableSlots = $availableSlots->filter(
                fn ($label) => str_contains(strtolower($label), strtolower($search))
            );
        }

        return $availableSlots->all();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (?string $state): string => ucfirst((string) $state))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('start_time')
                    ->label(__('Start'))
                    ->formatStateUsing(fn ($state) => static::formatTime($state)),
                TextColumn::make('end_time')
                    ->label(__('End'))
                    ->formatStateUsing(fn ($state) => static::formatTime($state)),
                TextColumn::make('provider.name')
                    ->label(__('Provider'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('note')
                    ->label(__('Note'))
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('provider_note')
                    ->label(__('Provider note'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('appointment_period')
                    ->label(__('Appointments'))
                    ->form([
                        Radio::make('period')
                            ->label(__('Show'))
                            ->options([
                                'upcoming' => 'Upcoming',
                                'past' => 'Past',
                            ])
                            ->default('upcoming')
                            ->inline(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $period = $data['period'] ?? 'upcoming';
                        $today = now()->toDateString();
                        $currentTime = now()->format('H:i:s');

                        return $query->where(function (Builder $q) use ($period, $today, $currentTime): void {
                            if ($period === 'past') {
                                $q->whereDate('date', '<', $today)
                                    ->orWhere(function (Builder $subQuery) use ($today, $currentTime): void {
                                        $subQuery->whereDate('date', $today)
                                            ->whereTime('end_time', '<', $currentTime);
                                    });

                                return;
                            }

                            $q->whereDate('date', '>', $today)
                                ->orWhere(function (Builder $subQuery) use ($today, $currentTime): void {
                                    $subQuery->whereDate('date', $today)
                                        ->whereTime('end_time', '>=', $currentTime);
                                });
                        });
                    })
                    ->indicateUsing(fn (array $data): string => ($data['period'] ?? 'upcoming') === 'past' ? 'Past appointments' : 'Upcoming appointments'),
            ])
            ->defaultSort('date', 'desc')
            ->emptyStateHeading(__('No appointment bookings yet'))
            ->emptyStateDescription(__('Appointment bookings for this user will appear here.'))
            ->emptyStateIcon(Heroicon::OutlinedCalendarDays)
            ->paginated([10, 25, 50])
            ->recordActions([
                Action::make('change_status')
                    ->label(__('Change status'))
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->color('gray')
                    ->fillForm(fn (SlotReservation $record): array => [
                        'status' => $record->status ?? 'pending',
                    ])
                    ->form([
                        Select::make('status')
                            ->label(__('Appointment status'))
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                    ])
                    ->action(function (SlotReservation $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                        ]);
                    }),
                EditAction::make()
                    ->label(__('Add / Edit note'))
                    ->tooltip('Add or edit provider note')
                    ->modalHeading(fn (SlotReservation $record) => 'Reservation – ' . $record->date?->format('M j, Y') . ' ' . static::formatTime($record->start_time))
                    ->fillForm(function ($livewire, SlotReservation $record, $table): array {
                        $data = $record->attributesToArray();
                        $data['provider'] = ['name' => $record->provider?->name];
                        return $data;
                    })
                    ->form(fn (Schema $schema): Schema => $this->form($this->defaultForm($schema))),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('New Appointment'))
                    ->modalHeading(__('Create Appointment Booking'))
                    ->modalWidth('6xl')
                    ->fillForm(function (): array {
                        $user = $this->getOwnerRecord();

                        $formData = [
                            'name'  => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone_number,
                        ];

                        if (Auth::check() && Auth::user()->hasRole('provider')) {
                            $formData['provider_id'] = Auth::id();
                        }

                        return $formData;
                    })
                    ->form(fn (Schema $schema): Schema => $this->createForm($schema))
                    ->mutateFormDataUsing(function (array $data): array {
                        $user = $this->getOwnerRecord();
                        $data['user_id'] = $user->id;

                        if (! empty($data['slot'])) {
                            [$start, $end] = explode('-', $data['slot']);
                            $data['start_time'] = $start;
                            $data['end_time'] = $end;
                        }

                        unset($data['slot']);

                        if (empty($data['name']) && $user->name) {
                            $data['name'] = $user->name;
                        }
                        if (empty($data['email']) && $user->email) {
                            $data['email'] = $user->email;
                        }
                        if (empty($data['phone']) && $user->phone_number) {
                            $data['phone'] = $user->phone_number;
                        }

                        return $data;
                    })
                    ->after(function (SlotReservation $record): void {
                        $provider = Provider::where('user_id', $record->provider_id)->first();
                        if (! $provider) {
                            return;
                        }

                        ProviderSlotOverride::updateOrCreate(
                            [
                                'provider_id' => $provider->id,
                                'date' => $record->date,
                                'start_time' => $record->start_time,
                                'end_time' => $record->end_time,
                            ],
                            [
                                'status' => SlotOverrideStatus::Reserved,
                                'reservation_id' => $record->id,
                            ]
                        );
                    }),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reservation details')
                    ->description(__('Read-only information for this appointment.'))
                    ->schema([
                        TextInput::make('date')
                            ->label(__('Date'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state) => $state ? (\is_object($state) && method_exists($state, 'format') ? $state->format('l, F j, Y') : $state) : '—'),
                        TextInput::make('start_time')
                            ->label(__('Start time'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state) => static::formatTime($state)),
                        TextInput::make('end_time')
                            ->label(__('End time'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state) => static::formatTime($state)),
                        TextInput::make('provider.name')
                            ->label(__('Provider'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('note')
                            ->label(__('Client / guest note'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Provider note')
                    ->description(__('You can edit these at any time.'))
                    ->schema([
                        Select::make('status')
                            ->label(__('Appointment status'))
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('provider_note')
                            ->label(__('Provider note'))
                            ->rows(4)
                            ->placeholder(__('Notes for this appointment'))
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public function createForm(Schema $schema): Schema
    {
        $user = $this->getOwnerRecord();

        $providerOptions = User::whereHas('roles', fn ($q) => $q->where('name', 'provider'))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        $isProviderLogin = Auth::check() && Auth::user()->hasRole('provider');

        return $schema
            ->components([
                Section::make('Appointment Details')
                    ->description($isProviderLogin
                        ? 'Pick a date on the calendar, then choose an available slot.'
                        : 'Select provider, pick a date on the calendar, then choose an available slot.')
                    ->schema([
                        Select::make('provider_id')
                            ->label(__('Provider'))
                            ->options($providerOptions)
                            ->required()
                            ->searchable()
                            ->placeholder(__('Select a provider'))
                            ->default($isProviderLogin ? Auth::id() : null)
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('date', null);
                                $set('slot', null);
                            })
                            ->visible(fn (): bool => ! $isProviderLogin)
                            ->columnSpanFull(),
                        Hidden::make('date')
                            ->required()
                            ->afterStateUpdated(fn (Set $set) => $set('slot', null)),
                        Hidden::make('slot')->required(),
                        ViewField::make('calendar')
                            ->label('')
                            ->view('filament.components.appointment-calendar-picker')
                            ->viewData(function (Get $get): array {
                                return [
                                    'selectedDate' => $get('date'),
                                    'minDate' => now()->startOfDay()->format('Y-m-d'),
                                    'statePath' => 'mountedActions.0.data',
                                ];
                            })
                            ->visible(fn (Get $get): bool => ! empty($get('provider_id')))
                            ->columnSpan(1),
                        Section::make('Available Slots')
                            ->description(fn (Get $get): ?string => self::getSlotsHelperText($get))
                            ->schema([
                                ViewField::make('slots_picker')
                                    ->label('')
                                    ->view('filament.components.appointment-slots-picker')
                                    ->viewData(function (Get $get): array {
                                        $providerUserId = $get('provider_id');
                                        $date = $get('date');
                                        $selectedSlot = $get('slot');
                                        $groupedSlots = [];
                                        if (! empty($providerUserId) && ! empty($date)) {
                                            $provider = Provider::with(['shifts', 'slotOverrides'])->where('user_id', $providerUserId)->first();
                                            if ($provider) {
                                                $dateCarbon = Carbon::parse($date);
                                                $slots = $provider->getSlotsForDate($dateCarbon)
                                                    ->filter(fn ($slot) => $slot['status'] === 'available');
                                                foreach ($slots as $slot) {
                                                    $shiftName = $slot['shift_name'] ?? 'Other';
                                                    if (! isset($groupedSlots[$shiftName])) {
                                                        $groupedSlots[$shiftName] = [];
                                                    }
                                                    $value = $slot['start'] . '-' . $slot['end'];
                                                    $groupedSlots[$shiftName][] = [
                                                        'value' => $value,
                                                        'label' => $slot['start'] . ' – ' . $slot['end'],
                                                        'timeRange' => $slot['start'] . '-' . $slot['end'],
                                                    ];
                                                }
                                            }
                                        }

                                        return [
                                            'groupedSlots' => $groupedSlots,
                                            'selectedSlot' => $selectedSlot,
                                            'statePath' => 'mountedActions.0.data',
                                        ];
                                    }),
                            ])
                            ->visible(fn (Get $get): bool => ! empty($get('provider_id')) && ! empty($get('date')))
                            ->columnSpan(1)
                            ->compact(),
                    ])
                    ->columns(2),
                Section::make('Client Information')
                    ->description(__('Pre-filled from user profile. You can override if needed.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->default($user->name)
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->default($user->email)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->default($user->phone_number)
                            ->maxLength(50),
                        Textarea::make('note')
                            ->label(__('Note'))
                            ->rows(2)
                            ->placeholder(__('Any notes for this appointment'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Section::make('Provider Notes')
                    ->description(__('Optional notes for this appointment.'))
                    ->schema([
                        Select::make('status')
                            ->label(__('Appointment Status'))
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('provider_note')
                            ->label(__('Provider Note'))
                            ->rows(3)
                            ->placeholder(__('Notes for this appointment')),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ])
            ->columns(1);
    }
}
