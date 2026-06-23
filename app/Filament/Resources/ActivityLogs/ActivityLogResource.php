<?php

namespace App\Filament\Resources\ActivityLogs;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\ActivityLogResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivityLogResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Activity::class;

    protected static ?int $navigationSort = 180;
    protected static ?string $permissionGroup = 'activity-log';
    protected static ?string $navigationLabel = 'Activity Logs';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?string $slug = 'activity-logs';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard';

    // Restrict to super_admin — logs contain sensitive data
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super_admin');
    }

    public static function canView(Model $record): bool
    {
        return auth()->check() && auth()->user()->hasRole('super_admin');
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Audit logs must not be deleted by anyone
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
    
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Activity Details'))
                    ->schema([
                        TextInput::make('log_name')
                            ->label(__('Log Name'))
                            ->disabled(),
                        TextInput::make('description')
                            ->label(__('Description'))
                            ->disabled(),
                        TextInput::make('subject_type')
                            ->label(__('Subject Type'))
                            ->disabled(),
                        TextInput::make('subject_id')
                            ->label(__('Subject ID'))
                            ->disabled(),
                        TextInput::make('causer_type')
                            ->label(__('Causer Type'))
                            ->disabled(),
                        TextInput::make('causer_id')
                            ->label(__('Causer ID'))
                            ->disabled(),
                        KeyValue::make('properties.attributes')
                            ->label(__('New Values'))
                            ->disabled(),
                        KeyValue::make('properties.old')
                            ->label(__('Old Values'))
                            ->disabled(),
                    ])->columns(2)->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with('causer'); // Eager load causer to prevent N+1 queries
            })
            ->columns([
                TextColumn::make('log_name')
                    ->label(__('Log Name'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('subject_type')
                    ->label(__('Model'))
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->searchable(),
                TextColumn::make('causer.name')
                    ->label(__('User'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->options(fn () => Activity::distinct()->pluck('log_name', 'log_name')),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
            // 'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Activity Log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activity Logs');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function getNavigationLabel(): string
    {
        return __('Activity Logs');
    }
}