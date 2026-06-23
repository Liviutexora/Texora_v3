<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\SlotReservation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Client';

    protected static ?string $pluralModelLabel = 'Clients';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return User::whereHas('roles', fn (Builder $q) => $q->where('name', 'client'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_index')
                    ->label(__('#'))
                    ->rowIndex()
                    ->width('3rem'),

                TextColumn::make('name')
                    ->label(__('Client'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record) => $record->email),

                TextColumn::make('phone_number')
                    ->label(__('Phone'))
                    ->searchable()
                    ->placeholder(__('—')),

                TextColumn::make('total_bookings')
                    ->label(__('Bookings'))
                    ->getStateUsing(fn (User $record) =>
                        SlotReservation::where('email', $record->email)->count()
                    )
                    ->badge()
                    ->color('info')
                    ->sortable(false),

                TextColumn::make('businesses_booked')
                    ->label(__('Businesses'))
                    ->getStateUsing(fn (User $record) =>
                        SlotReservation::where('email', $record->email)
                            ->distinct('tenant_id')
                            ->count('tenant_id')
                    )
                    ->badge()
                    ->color('warning')
                    ->sortable(false),

                TextColumn::make('last_booking')
                    ->label(__('Last Booking'))
                    ->getStateUsing(fn (User $record) =>
                        SlotReservation::where('email', $record->email)
                            ->max('date')
                    )
                    ->date('d M Y')
                    ->placeholder(__('—'))
                    ->sortable(false),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label(__('Registered'))
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Account Status'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Inactive')),
            ])
            ->recordActions([
                Action::make('impersonate')
                    ->label('')
                    ->tooltip(__('View My Bookings as this client'))
                    ->icon('heroicon-o-identification')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('Impersonate client?'))
                    ->modalDescription(fn (User $record) =>
                        __('You will be taken to the My Bookings page as :name (:email). A banner will let you exit at any time.', ['name' => $record->name, 'email' => $record->email])
                    )
                    ->modalSubmitActionLabel(__('Impersonate'))
                    ->action(fn (User $record) => redirect()->route('impersonate.client', $record->id))
                    ->visible(fn () => auth()->user()?->hasRole('super_admin')),

                Action::make('reset_password')
                    ->label('')
                    ->tooltip(__('Reset password'))
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('Reset client password'))
                    ->modalDescription(fn (User $record) =>
                        __('Set a new password for') . ' ' . $record->email
                    )
                    ->form([
                        \Filament\Forms\Components\TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->confirmed(),
                        \Filament\Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required()
                            ->label(__('Confirm Password')),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update(['password' => Hash::make($data['password'])]);
                        Notification::make()
                            ->title(__('Password updated'))
                            ->success()
                            ->send();
                    }),

                Action::make('toggle_active')
                    ->label('')
                    ->tooltip(fn (User $record) => $record->is_active ? __('Deactivate account') : __('Activate account'))
                    ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => $record->is_active ? __('Deactivate account?') : __('Activate account?'))
                    ->modalDescription(fn (User $record) => $record->is_active
                        ? __('This will block :name from logging in. Their booking history is kept. You can reactivate at any time.', ['name' => $record->name])
                        : __('This will restore login access for :name. They will be able to view their bookings again.', ['name' => $record->name])
                    )
                    ->modalSubmitActionLabel(fn (User $record) => $record->is_active ? __('Yes, deactivate') : __('Yes, activate'))
                    ->action(function (User $record) {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('Account activated') : __('Account deactivated'))
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('')
                    ->tooltip(__('Delete client')),
            ])
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading(__('No clients yet'))
            ->emptyStateDescription(__('Clients are created when a business owner adds a booking guest as a registered user.'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
        ];
    }

    // Clients are managed through the booking flow — no standalone create page
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getModelLabel(): string
    {
        return __('Client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Clients');
    }

    public static function getNavigationLabel(): string
    {
        return __('Clients');
    }
}
