<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ProviderResource\Pages;
use App\Filament\Tenant\Resources\ProviderResource\RelationManagers\BlockedDatesRelationManager;
use App\Filament\Tenant\Resources\ProviderResource\RelationManagers\ProviderShiftsRelationManager;
use App\Filament\Tenant\Resources\ProviderResource\RelationManagers\ProviderServicesRelationManager;
use App\Models\Provider;
use App\Support\TenantContext;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProviderResource extends Resource
{
    protected static ?string $model = Provider::class;

    public static function getModelLabel(): string
    {
        return __('Provider');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Providers');
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Staff Member'))
                ->description(__('Link this provider profile to a staff user account.'))
                ->schema([
                    Select::make('user_id')
                        ->label(__('Staff Member (User Account)'))
                        ->options(function (?\App\Models\Provider $record) {
                            $tenantId = \App\Support\TenantContext::id()
                                ?? session('impersonate_tenant_id')
                                ?? \App\Models\Tenant::where('owner_id', auth()->id())->value('id');

                            if (! $tenantId) {
                                return collect();
                            }

                            // IDs already assigned to a provider for this tenant
                            $takenIds = \App\Models\Provider::where('tenant_id', $tenantId)
                                ->pluck('user_id');

                            // When editing, always keep the current user selectable
                            if ($record?->user_id) {
                                $takenIds = $takenIds->reject(fn ($id) => $id === $record->user_id);
                            }

                            return \App\Models\User::where('tenant_id', $tenantId)
                                ->whereNotIn('id', $takenIds)
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->required(),
                ]),

            Section::make(__('Profile'))
                ->description(__('Professional details shown to clients on the booking page.'))
                ->columns(2)
                ->schema([
                    TextInput::make('job_title')
                        ->label(__('Job Title'))
                        ->maxLength(255)
                        ->placeholder(__('e.g. Senior Stylist, Head Trainer')),

                    TextInput::make('experience_years')
                        ->label(__('Years of Experience'))
                        ->numeric()
                        ->minValue(0),

                    Textarea::make('bio')
                        ->label(__('Bio'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('job_title')->label(__('Title'))->searchable(),
                TextColumn::make('experience_years')->label(__('Exp.'))->suffix(' yrs')->sortable(),
                TextColumn::make('services_count')->label(__('Services'))->counts('services'),
                TextColumn::make('bookings_count')->label(__('Bookings (all time)'))->counts('bookings'),
            ])
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

    public static function getRelationManagers(): array
    {
        return [
            ProviderServicesRelationManager::class,
            ProviderShiftsRelationManager::class,
            BlockedDatesRelationManager::class,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Offerings');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProviders::route('/'),
            'create' => Pages\CreateProvider::route('/create'),
            'edit'   => Pages\EditProvider::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool {
        $user = auth()->user();
        return $user && (!$user->hasRole("staff") || $user->hasAnyRole(["super_admin", "tenant_owner", "provider"]));
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return true; }

    public static function canCreate(): bool
    {
        $tenant = TenantContext::current();
        $plan   = $tenant?->plan;

        if ($plan && $plan->max_providers !== null) {
            $current = \App\Models\Provider::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->count();

            if ($current >= $plan->max_providers) {
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

        $query = parent::getEloquentQuery()->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId);

        $user = auth()->user();
        if ($user && $user->hasRole('provider') && ! $user->hasAnyRole(['super_admin', 'tenant_owner'])) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
