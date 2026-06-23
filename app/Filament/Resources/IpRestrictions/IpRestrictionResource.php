<?php

namespace App\Filament\Resources\IpRestrictions;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\IpRestrictions\Pages\ListIpRestrictions;
use App\Filament\Resources\IpRestrictions\Pages\CreateIpRestriction;
use App\Filament\Resources\IpRestrictions\Pages\EditIpRestriction;
use App\Filament\Resources\IpRestrictionResource\Pages;
use App\Filament\Concerns\HasResourcePermissions;
use App\Models\IpRestriction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IpRestrictionResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = IpRestriction::class;
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'IP Restrictions';

    public static function getModelLabel(): string
    {
        return __('Restriction');
    }

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-exclamation';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Restriction Details'))
                    ->schema([
                        TextInput::make('ip_address')
                            ->required()
                            ->label(__('IP Address'))
                            ->placeholder(__('192.168.1.1')),
                        Select::make('user_id')
                            ->label(__('User'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->nullable()
                            ->helperText(__('Leave empty for global restriction')),
                        Textarea::make('reason')
                            ->label(__('Reason'))
                            ->rows(3),
                        Toggle::make('is_active')
                            ->label(__('Is Active'))
                            ->default(true),
                        DateTimePicker::make('expires_at')
                            ->label(__('Expires At'))
                            ->nullable()
                            ->helperText(__('Leave empty for permanent restriction')),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with('user'); // Eager load user to prevent N+1 queries
            })
            ->columns([
                TextColumn::make('ip_address')
                    ->label(__('IP Address'))
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->placeholder(__('Global')),
                TextColumn::make('reason')
                    ->label(__('Reason'))
                    ->limit(50),
                IconColumn::make('is_active')
                    ->label(__('Is Active'))
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->label(__('Expires At'))
                    ->dateTime()
                    ->placeholder(__('Never')),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'whitelist' => 'Whitelist',
                        'blacklist' => 'Blacklist',
                    ]),
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIpRestrictions::route('/'),
            'create' => CreateIpRestriction::route('/create'),
            'edit' => EditIpRestriction::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('IP Restrictions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('IP Restrictions');
    }
}
