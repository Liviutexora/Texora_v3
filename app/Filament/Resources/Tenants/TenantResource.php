<?php

namespace App\Filament\Resources\Tenants;

use App\Filament\Resources\Tenants\Pages;
use App\Models\ImpersonationLog;
use App\Models\SlotReservation;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\LocalisationOptions;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $modelLabel = 'Business';

    protected static ?string $pluralModelLabel = 'Businesses';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Core info ─────────────────────────────────────────────
            Section::make(__('Business Details'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('Business Name'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set, string $operation) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null
                        ),

                    TextInput::make('slug')
                        ->label(__('URL Slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(Tenant::class, 'slug', ignoreRecord: true)
                        ->helperText(fn ($record) => $record
                            ? 'Booking URL: ' . url('/' . $record->slug)
                            : 'Will become the booking page URL e.g. /my-salon'),

                    // Owner lives here — not a separate orphan panel
                    Select::make('owner_id')
                        ->label(__('Owner'))
                        ->options(fn (?Tenant $record) => User::whereHas('roles', fn ($q) => $q->where('name', 'tenant_owner'))
                            ->whereNotIn(
                                'id',
                                Tenant::whereNotNull('owner_id')
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->pluck('owner_id')
                            )
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->getOptionLabelUsing(fn ($value) => User::find($value)?->name ?? "User #{$value}")
                        ->searchable()
                        ->placeholder(__('Search by name…'))
                        ->createOptionForm([
                            TextInput::make('name')->required()->maxLength(255),
                            TextInput::make('email')->email()->required()->unique(User::class, 'email'),
                            TextInput::make('password')->password()->required()->minLength(8),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return User::create([
                                'name'     => $data['name'],
                                'email'    => $data['email'],
                                'password' => bcrypt($data['password']),
                            ])->id;
                        })
                        ->required()
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated(fn (string $operation) => $operation === 'create')
                        ->columnSpanFull(),

                    Select::make('status')
                        ->options([
                            'active'    => 'Active',
                            'suspended' => 'Suspended',
                        ])
                        ->default('active')
                        ->required(),

                    Select::make('plan_id')
                        ->label(__('Plan'))
                        ->options(fn () => SubscriptionPlan::where('is_active', true)
                            ->orderBy('sort_order')
                            ->get()
                            ->mapWithKeys(fn ($p) => [
                                $p->id => $p->name . ' — ' . $p->priceDisplay(),
                            ]))
                        ->searchable()
                        ->placeholder(__('No plan')),

                    TextInput::make('email')->label(__('Business Email'))->email()->maxLength(255),
                    TextInput::make('phone')->label(__('Phone'))->maxLength(50),
                    TextInput::make('website_url')->label(__('Website'))->url()->maxLength(255)->placeholder(__('https://')),
                    TextInput::make('address')->label(__('Address'))->maxLength(255),
                    TextInput::make('city')->label(__('City'))->maxLength(100),
                    TextInput::make('country')->label(__('Country'))->maxLength(100),
                ]),

            // ── Optional sections — all collapsible, collapsed by default ──
            Section::make(__('Booking Page'))
                ->description(__('Customise how the public booking page looks.'))
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('booking_page_tagline')
                        ->label(__('Tagline'))
                        ->maxLength(255)
                        ->placeholder(__('e.g. Zero wait time, premium results.'))
                        ->columnSpanFull(),

                    ColorPicker::make('booking_page_color')
                        ->label(__('Brand Colour')),
                ]),

            Section::make(__('Trial & Subscription'))
                ->description(__('Stripe billing details and trial period override.'))
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema([
                    DateTimePicker::make('trial_ends_at')
                        ->label(__('Trial Ends At'))
                        ->helperText(__('Extend or shorten the free trial manually')),

                    Select::make('stripe_subscription_status')
                        ->label(__('Stripe Status'))
                        ->options([
                            'trialing' => 'Trialing',
                            'active'   => 'Active',
                            'past_due' => 'Past Due',
                            'canceled' => 'Canceled',
                            'unpaid'   => 'Unpaid',
                            'paused'   => 'Paused',
                        ])
                        ->placeholder(__('— not set —')),

                    TextInput::make('stripe_customer_id')
                        ->label(__('Stripe Customer ID'))
                        ->placeholder(__('cus_...'))
                        ->maxLength(255),

                    TextInput::make('stripe_subscription_id')
                        ->label(__('Stripe Subscription ID'))
                        ->placeholder(__('sub_...'))
                        ->maxLength(255),
                ]),

            Section::make(__('Localisation'))
                ->description(__('Timezone and currency for this business.'))
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema([
                    Select::make('timezone')
                        ->label(__('Timezone'))
                        ->options(fn () => LocalisationOptions::timezoneSelectOptions())
                        ->searchable()
                        ->default('UTC'),

                    Select::make('currency')
                        ->label(__('Currency'))
                        ->options(fn () => LocalisationOptions::currencies())
                        ->default('USD')
                        ->searchable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->city ? $record->city . ($record->country ? ', ' . $record->country : '') : null),

                TextColumn::make('owner.name')
                    ->label(__('Owner'))
                    ->searchable()
                    ->description(fn ($record) => $record->owner?->email),

                TextColumn::make('plan.name')
                    ->label(__('Plan'))
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state ?? '—')
                    ->default('—'),

                TextColumn::make('stripe_subscription_status')
                    ->label(__('Stripe'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active'   => 'success',
                        'trialing' => 'warning',
                        'past_due' => 'danger',
                        'canceled', 'unpaid' => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state)) : '—'),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active'    => 'success',
                        'suspended' => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('trial_ends_at')
                    ->label(__('Trial Ends'))
                    ->since()
                    ->sortable()
                    ->placeholder(__('—'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bookings_count')
                    ->label(__('Bookings'))
                    ->counts('bookings')
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('Booking URL'))
                    ->url(fn ($record) => url("/{$record->slug}"))
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('Joined'))
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active'    => 'Active',
                        'suspended' => 'Suspended',
                    ]),
                SelectFilter::make('stripe_subscription_status')
                    ->label(__('Stripe Status'))
                    ->options([
                        'trialing' => 'Trialing',
                        'active'   => 'Active',
                        'past_due' => 'Past Due',
                        'canceled' => 'Canceled',
                        'unpaid'   => 'Unpaid',
                    ]),
                SelectFilter::make('plan_id')
                    ->label(__('Plan'))
                    ->relationship('plan', 'name'),
            ])
            ->actions([
                EditAction::make(),

                Action::make('impersonate')
                    ->label(__('Impersonate'))
                    ->icon('heroicon-o-user-circle')
                    ->color('warning')
                    ->tooltip(__('Log in as this business owner and manage their panel'))
                    ->action(function ($record) {
                        ImpersonationLog::create([
                            'admin_id'   => auth()->id(),
                            'tenant_id'  => $record->id,
                            'ip_address' => request()->ip(),
                            'started_at' => now(),
                        ]);
                        session(['impersonate_tenant_id' => $record->id]);
                        return redirect('/manage');
                    }),

                Action::make('view_booking_page')
                    ->label(__('Booking Page'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->tooltip(__('Open public booking page'))
                    ->url(fn ($record) => url("/{$record->slug}"))
                    ->openUrlInNewTab(),

                Action::make('gdpr_export')
                    ->label(__('GDPR Export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->tooltip(__('Download all client data for this business (GDPR)'))
                    ->action(function ($record): StreamedResponse {
                        $bookings = SlotReservation::withoutGlobalScope('tenant')
                            ->where('tenant_id', $record->id)
                            ->with('service')
                            ->orderBy('date')
                            ->get();

                        $export = [
                            'exported_at' => now()->toIso8601String(),
                            'tenant'      => $record->toArray(),
                            'bookings'    => $bookings->map(fn ($b) => [
                                'id'             => $b->id,
                                'client_name'    => $b->name,
                                'client_email'   => $b->email,
                                'client_phone'   => $b->phone,
                                'service'        => $b->service?->name,
                                'date'           => $b->date?->toDateString(),
                                'time'           => substr($b->start_time ?? '', 0, 5),
                                'status'         => $b->status,
                                'note'           => $b->note,
                                'custom_answers' => $b->custom_answers,
                                'created_at'     => $b->created_at?->toIso8601String(),
                            ])->toArray(),
                        ];

                        return response()->streamDownload(function () use ($export) {
                            echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        }, "gdpr-export-{$record->slug}-" . now()->format('Y-m-d') . '.json', [
                            'Content-Type' => 'application/json',
                        ]);
                    }),

                Action::make('activate')
                    ->label(__('Activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'active'])),

                Action::make('suspend')
                    ->label(__('Suspend'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'suspended')
                    ->requiresConfirmation()
                    ->modalDescription(__('This will immediately block the business\'s booking page.'))
                    ->action(fn ($record) => $record->update(['status' => 'suspended'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {
                            foreach ($records as $record) {
                                if ($record->bookings()->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title(__('Cannot delete — business has bookings'))
                                        ->body("\"{$record->name}\" has booking history. Export GDPR data first or reassign bookings.")
                                        ->persistent()
                                        ->send();
                                    $action->cancel();
                                    return;
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit'   => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Platform');
    }

    public static function getModelLabel(): string
    {
        return __('Business');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Businesses');
    }

    public static function getNavigationLabel(): string
    {
        return __('Businesses');
    }
}
