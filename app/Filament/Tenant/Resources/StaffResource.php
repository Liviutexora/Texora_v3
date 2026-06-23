<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\StaffResource\Pages;
use App\Models\User;
use App\Support\TenantContext;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getModelLabel(): string
    {
        return __('Staff Member');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Staff');
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 10;

    public static function canViewAny(): bool { return auth()->check() && ! auth()->user()->hasRole('staff'); }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = TenantContext::id();

        if (! $tenantId) {
            return parent::getEloquentQuery()->whereRaw('0 = 1');
        }

        return parent::getEloquentQuery()
            ->where('tenant_id', $tenantId);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Account Details'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('phone_number')
                        ->label(__('Phone'))
                        ->maxLength(50),

                    Select::make('is_active')
                        ->label(__('Status'))
                        ->options([
                            1 => __('Active'),
                            0 => __('Inactive'),
                        ])
                        ->default(1)
                        ->required(),
                ]),

            Section::make(__('Password'))
                ->columns(2)
                ->schema([
                    TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->minLength(8)
                        ->label(fn (string $operation) => $operation === 'create' ? __('Password') : __('New Password'))
                        ->helperText(fn (string $operation) => $operation === 'edit' ? __('Leave blank to keep current password') : null),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('email')->label(__('Email'))->searchable()->copyable(),
                TextColumn::make('phone_number')->label(__('Phone'))->default('—'),
                IconColumn::make('is_active')->label(__('Active'))->boolean(),
                TextColumn::make('provider.job_title')->label(__('Role/Title'))->default('—'),
                TextColumn::make('created_at')->label(__('Added'))->date('d M Y')->sortable(),
            ])
            ->defaultSort('name')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Account');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit'   => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
