<?php

namespace App\Filament\Resources\SubscriptionPlans;

use App\Filament\Resources\SubscriptionPlans\Pages;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanPrice;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $modelLabel = 'Plan';

    protected static ?string $pluralModelLabel = 'Subscription Plans';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Plan Details ───────────────────────────────────────────────
            Section::make(__('Plan Details'))
                ->schema([
                    Grid::make(2)->schema([

                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set, string $operation) =>
                                $operation === 'create'
                                    ? $set('slug', \Illuminate\Support\Str::slug($state))
                                    : null
                            ),

                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->alphaDash()
                            ->maxLength(100)
                            ->helperText(__('Used in URLs — lowercase, numbers and hyphens only')),

                        TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('Lower number = shown first on the registration page')),

                        Toggle::make('is_active')
                            ->label(__('Active (visible on registration page)'))
                            ->default(true)
                            ->columnSpan(1),

                        TextInput::make('stripe_product_id')
                            ->label(__('Stripe Product ID'))
                            ->readOnly()
                            ->placeholder(__('Auto-generated on save'))
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->helperText(__('The Stripe Product this plan maps to — managed automatically'))
                            ->columnSpanFull(),
                    ]),
                ]),

            // ── Plan Limits ────────────────────────────────────────────────
            Section::make(__('Plan Limits'))
                ->description(__('Leave blank for unlimited.'))
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('max_providers')
                            ->label(__('Max Providers'))
                            ->numeric()
                            ->minValue(1)
                            ->placeholder(__('Unlimited')),

                        TextInput::make('max_services')
                            ->label(__('Max Services'))
                            ->numeric()
                            ->minValue(1)
                            ->placeholder(__('Unlimited')),

                        TextInput::make('max_bookings_per_month')
                            ->label(__('Max Bookings / Month'))
                            ->numeric()
                            ->minValue(1)
                            ->placeholder(__('Unlimited')),
                    ]),
                ]),

            // ── Billing Cycles & Pricing ──────────────────────────────────
            Section::make(__('Billing Cycles & Pricing'))
                ->description(__('Add one row per billing cycle. Stripe prices are auto-created on save. Drag rows to reorder.'))
                ->schema([
                    Repeater::make('prices')
                        ->relationship('prices')
                        ->schema([
                            Grid::make(3)->schema([

                                Select::make('billing_cycle')
                                    ->label(__('Billing Cycle'))
                                    ->options([
                                        'monthly' => 'Monthly',
                                        'yearly'  => 'Annually',
                                        'weekly'  => 'Weekly',
                                    ])
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->label(__('Price'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('$')
                                    ->required()
                                    ->helperText(__('Full price for this cycle'))
                                    ->columnSpan(1),

                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->columnSpan(1)
                                    ->inline(false),

                                // Stripe Price ID — read-only, auto-generated on save
                                TextInput::make('stripe_price_id')
                                    ->label(__('Stripe Price ID'))
                                    ->readOnly()
                                    ->hidden(fn (string $operation): bool => $operation === 'create')
                                    ->placeholder(__('Auto-generated on save'))
                                    ->helperText(__('Managed automatically — created in Stripe when this plan is saved.'))
                                    ->columnSpanFull(),
                            ]),
                        ])
                        ->addActionLabel(__('Add billing cycle'))
                        ->reorderable('sort_order')
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ]),

            // ── Features List ──────────────────────────────────────────────
            Section::make(__('Features List'))
                ->description(__('These bullet points appear on the signup page plan card.'))
                ->schema([
                    Repeater::make('features')
                        ->schema([
                            TextInput::make('text')
                                ->label('')
                                ->placeholder(__('e.g. Unlimited bookings per month'))
                                ->required(),
                        ])
                        ->addActionLabel(__('Add feature'))
                        ->reorderable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label(__('#'))
                    ->sortable()
                    ->width('48px'),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->slug),

                // Show all active prices inline: "$29/mo · $278/yr"
                TextColumn::make('prices_summary')
                    ->label(__('Pricing'))
                    ->getStateUsing(function (SubscriptionPlan $record): string {
                        $prices = $record->activePrices()->get();
                        if ($prices->isEmpty()) {
                            return $record->price ? '$' . number_format((float) $record->price, 0) : 'Free';
                        }
                        return $prices->map(fn ($p) =>
                            '$' . number_format((float) $p->price, 0) . '/' . $p->intervalShort()
                        )->implode(' · ');
                    }),

                // Billing cycles as badges
                TextColumn::make('billing_cycles')
                    ->label(__('Cycles'))
                    ->getStateUsing(function (SubscriptionPlan $record): string {
                        return $record->activePrices()
                            ->pluck('billing_cycle')
                            ->map(fn ($c) => ucfirst($c))
                            ->implode(', ') ?: '—';
                    }),

                TextColumn::make('max_providers')
                    ->label(__('Providers'))
                    ->default('∞'),

                TextColumn::make('max_services')
                    ->label(__('Services'))
                    ->default('∞'),

                TextColumn::make('max_bookings_per_month')
                    ->label(__('Bookings/mo'))
                    ->default('∞'),

                TextColumn::make('tenants_count')
                    ->label(__('Businesses'))
                    ->counts('tenants')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label(__('Active'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Status'))
                    ->placeholder(__('All plans'))
                    ->trueLabel(__('Active only'))
                    ->falseLabel(__('Inactive only')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (SubscriptionPlan $record, DeleteAction $action) {
                        if ($record->tenants()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title(__('Cannot delete plan'))
                                ->body("{$record->tenants()->count()} business(es) are on this plan. Reassign them first.")
                                ->persistent()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {
                            foreach ($records as $record) {
                                if ($record->tenants()->exists()) {
                                    Notification::make()
                                        ->danger()
                                        ->title(__('Cannot delete — plan has active businesses'))
                                        ->body(__('Deselect plans that have businesses assigned before deleting.'))
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
            'index'  => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit'   => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Platform');
    }

    public static function getModelLabel(): string
    {
        return __('Plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Subscription Plans');
    }

    public static function getNavigationLabel(): string
    {
        return __('Subscription Plans');
    }
}
