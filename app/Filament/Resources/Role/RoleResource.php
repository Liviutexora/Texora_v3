<?php

namespace App\Filament\Resources\Role;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Role\Pages\CreateRole;
use App\Filament\Resources\Role\Pages\EditRole;
use App\Filament\Resources\Role\Pages\ListRoles;
use App\Filament\Resources\Role\Pages\ViewRole;
use App\Models\Role;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use BezhanSalleh\PluginEssentials\Concerns\Resource as Essentials;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    use Essentials\BelongsToParent;
    use Essentials\BelongsToTenant;
    use Essentials\HasGlobalSearch;
    use Essentials\HasLabels;
    use Essentials\HasNavigation;
    use HasResourcePermissions;
    use HasShieldFormComponents;

    // --- Model and Navigation Setup ---
    protected static ?string $model = Role::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Roles & Permissions';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make(__('Role Details'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Role Name'))
                                    // Removed Shield-specific unique rule logic
                                    ->disabled(fn ($record) => in_array($record?->name, ['super_admin', 'provider', 'client']))
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('guard_name')
                                    ->label(__('Guard Name'))
                                    ->default('web') // Default to 'web' unless you use a different Spatie guard
                                    ->nullable()
                                    ->maxLength(255),

                                Select::make(config('permission.column_names.team_foreign_key'))
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    /** @phpstan-ignore-next-line */
                                    ->default(Filament::getTenant()?->id)
                                    ->options(fn (): array => in_array(Utils::getTenantModel(), [null, '', '0'], true) ? [] : Utils::getTenantModel()::pluck('name', 'id')->toArray())
                                    ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled())
                                    ->dehydrated(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                                static::getSelectAllFormComponent(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                static::getShieldFormComponents(),
                // Permission Assignment Component goes here if you add one manually
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->weight('font-medium')
                    ->label(__('Name'))
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label(__('Guard')),
                // Removed Shield-specific columns: team.name and permissions_count
                TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => $record->name !== 'super_admin'),
                DeleteAction::make()
                    ->visible(fn ($record) => ! in_array($record->name, ['super_admin', 'provider', 'client'])),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    // --- Authorization Checks ---

    public static function canDelete($record): bool
    {
        return ! in_array($record->name, ['super_admin', 'provider', 'client']);
    }

    public static function canEdit($record): bool
    {
        // super_admin: no edit
        if ($record->name === 'super_admin') {
            return false;
        }

        return true;
    }

    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }

    public static function getCluster(): ?string
    {
        return Utils::getResourceCluster();
    }

    public static function getEssentialsPlugin(): ?FilamentShieldPlugin
    {
        return FilamentShieldPlugin::get();
    }

    /**
     * Override navigation group to use custom group instead of Shield's default
     */
    public static function getModelLabel(): string
    {
        return __('Role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Roles & Permissions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Roles & Permissions');
    }

    /** Filament coalesces null sort to -1; Shield delegation can return null—use explicit sort. */
    public static function getNavigationSort(): ?int
    {
        return static::$navigationSort;
    }
}
