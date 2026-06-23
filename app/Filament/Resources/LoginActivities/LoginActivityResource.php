<?php

namespace App\Filament\Resources\LoginActivities;

use App\Filament\Resources\LoginActivities\Pages\ListLoginActivities;
use App\Models\LoginActivity;
use BackedEnum;
use Filament\Actions\ViewAction;
use App\Filament\Concerns\HasResourcePermissions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;

class LoginActivityResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = LoginActivity::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Login Activity';
    protected static string|\UnitEnum|null $navigationGroup = 'User Management';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-m-computer-desktop';
    
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with('user'); // Eager load user to prevent N+1 queries
            })
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->user) {
                            return 'N/A';
                        }
                        $user = $record->user;
                        return $user->name 
                            ? "{$user->name} ({$user->email})"
                            : $user->email;
                    })
                    ->sortable()
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('ip_address')->label(__('IP Address'))->sortable()->searchable(),
                TextColumn::make('user_agent')->label(__('User Agent'))->limit(50)->wrap(),
                TextColumn::make('logged_in_at')->label(__('Logged In'))->dateTime()->sortable(),
                TextColumn::make('logged_out_at')->label(__('Logged Out'))->dateTime()->sortable(),
            ])
            ->actions([
                // ViewAction::make(), // View only
            ])
            ->bulkActions([])
            ->defaultSort('logged_in_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginActivities::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Login Activity');
    }
}
