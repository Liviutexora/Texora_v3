<?php

namespace App\Filament\Tenant\Resources\ProviderResource\RelationManagers;

use App\Support\TenantContext;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProviderShiftsRelationManager extends RelationManager
{
    protected static string $relationship = 'shifts';

    protected static ?string $title = 'Working Shifts';

    public function form(Schema $schema): Schema
    {
        $days = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
        ];

        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('name')->required()->columnSpan(2),
                TimePicker::make('start_time')->required()->seconds(false),
                TextInput::make('slot_duration_minutes')
                    ->label(__('Duration (min)'))
                    ->numeric()->required()->minValue(5)->default(30),
                TextInput::make('number_of_slots')
                    ->label(__('No. of Slots'))
                    ->numeric()->required()->minValue(1)->default(8),
                TextInput::make('buffer_minutes')
                    ->label(__('Buffer (min)'))
                    ->numeric()->default(0),
                CheckboxList::make('available_days')
                    ->label(__('Available Days'))
                    ->options($days)
                    ->columns(4)
                    ->required()
                    ->columnSpan(2),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        $days = [
            1 => 'Mon', 2 => 'Tue', 3 => 'Wed',
            4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun',
        ];

        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('start_time')->label(__('Start'))->time('H:i'),
                TextColumn::make('slot_duration_minutes')->label(__('Duration'))->suffix(' min'),
                TextColumn::make('number_of_slots')->label(__('Slots')),
                TextColumn::make('available_days')
                    ->label(__('Days'))
                    ->formatStateUsing(fn ($state) => collect((array) $state)
                        ->map(fn ($d) => $days[$d] ?? $d)->join(', ')),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([DeleteBulkAction::make()]);
    }
}
