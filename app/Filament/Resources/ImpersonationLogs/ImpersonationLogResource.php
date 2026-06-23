<?php

namespace App\Filament\Resources\ImpersonationLogs;

use App\Filament\Resources\ImpersonationLogs\Pages;
use App\Models\ImpersonationLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImpersonationLogResource extends Resource
{
    protected static ?string $model = ImpersonationLog::class;

    protected static ?string $modelLabel = 'Impersonation Log';

    protected static ?string $pluralModelLabel = 'Impersonation Audit Log';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 5;

    // Hidden from nav — data is still recorded for audit/compliance, but the page
    // adds no day-to-day value and would confuse buyers of this product.
    protected static bool $shouldRegisterNavigation = false;

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('admin.name')
                    ->label(__('Admin'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('admin.email')
                    ->label(__('Admin Email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tenant.name')
                    ->label(__('Business'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label(__('IP Address'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('started_at')
                    ->label(__('Started'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->label(__('Ended'))
                    ->dateTime('d M Y H:i')
                    ->placeholder(__('Active / not recorded'))
                    ->sortable(),
                TextColumn::make('duration')
                    ->label(__('Duration'))
                    ->state(fn ($record) => $record->ended_at
                        ? $record->started_at->diffForHumans($record->ended_at, true)
                        : '—'),
            ])
            ->defaultSort('started_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImpersonationLogs::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Platform');
    }

    public static function getModelLabel(): string
    {
        return __('Impersonation Log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Impersonation Audit Log');
    }
}
