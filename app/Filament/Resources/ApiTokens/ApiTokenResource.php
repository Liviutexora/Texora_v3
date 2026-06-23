<?php

namespace App\Filament\Resources\ApiTokens;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ApiTokens\Pages\ListApiTokens;
use App\Filament\Resources\ApiTokens\Pages\CreateApiToken;
use App\Filament\Resources\ApiTokens\Pages\EditApiToken;
use App\Filament\Resources\ApiTokenResource\Pages;
use App\Models\ApiToken;
use Filament\Forms;
use Filament\Resources\Resource;
use App\Filament\Concerns\HasResourcePermissions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ApiTokenResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = ApiToken::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'API Tokens';
    protected static ?int $navigationSort = 25;
    protected static string|\UnitEnum|null $navigationGroup = 'User Management';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('User'))
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('name')->label(__('Name'))->required(),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'active' => __('Active'),
                        'inactive' => __('Inactive'),
                        'revoked' => __('Revoked'),
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with('user'); // Eager load user to prevent N+1 queries
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')->label(__('Owner')),
                TextColumn::make('token_prefix')
                    ->label(__('Token Prefix'))
                    ->formatStateUsing(fn ($state) => $state ? "{$state}••••••••••••••••••••" : '—')
                    ->tooltip('Full token is only shown once at creation'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'warning',
                        'revoked'  => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('created_at')->date(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'revoked' => 'Revoked',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiTokens::route('/'),
            'create' => CreateApiToken::route('/create'),
            'edit' => EditApiToken::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('API Tokens');
    }
}
