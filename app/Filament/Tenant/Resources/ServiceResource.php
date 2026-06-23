<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Support\TenantContext;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('Service');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Services');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Service Details'))
                ->description(__('Basic information shown to customers on the booking page.'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label(__('Description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make(__('Pricing & Duration'))
                ->description(__('How long the service takes and what it costs.'))
                ->columns(2)
                ->schema([
                    TextInput::make('duration_minutes')
                        ->label(__('Duration (minutes)'))
                        ->required()
                        ->numeric()
                        ->minValue(5)
                        ->maxValue(480)
                        ->default(30)
                        ->suffix('min'),

                    TextInput::make('price')
                        ->label(__('Price'))
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix(fn () => TenantContext::current()?->currency ?? 'INR'),
                ]),

            Section::make(__('Visibility'))
                ->columns(2)
                ->schema([
                    TextInput::make('sort_order')
                        ->label(__('Display Order'))
                        ->numeric()
                        ->default(0)
                        ->helperText(__('Lower numbers appear first.')),

                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->helperText(__('Inactive services are hidden from the booking page.'))
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('duration_minutes')
                    ->label(__('Duration'))
                    ->formatStateUsing(fn ($state) => $state < 60
                        ? "{$state} min"
                        : intdiv($state, 60) . 'h ' . ($state % 60 > 0 ? $state % 60 . 'min' : '')),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                TextColumn::make('providers_count')
                    ->label(__('Providers'))
                    ->counts('providers'),
                IconColumn::make('is_active')->boolean()->label(__('Active')),
                TextColumn::make('sort_order')->label(__('Order'))->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Offerings');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool { return auth()->check() && ! auth()->user()->hasRole('staff'); }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function canCreate(): bool
    {
        $tenant = TenantContext::current();
        $plan   = $tenant?->plan;

        if ($plan && $plan->max_services !== null) {
            $current = \App\Models\Service::where('tenant_id', $tenant->id)->count();

            if ($current >= $plan->max_services) {
                return false;
            }
        }

        return true;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return parent::getEloquentQuery()->whereRaw('0 = 1');
        }

        return parent::getEloquentQuery()->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId);
    }
}
