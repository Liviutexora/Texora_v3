<?php

namespace App\Filament\Tenant\Widgets;

use App\Filament\Tenant\Resources\ClientResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CustomerReturnsTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = null;

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return __('Clienți programați pentru reminder');
    }

    public function table(Table $table): Table
    {
        // Reuse the exact same Table Builder used by Clients page.
        $table = ClientResource::table($table);

        $columns = array_map(function ($column) {
            return match ($column->getName()) {
                'total_bookings'   => $column->label(__('Reminder')),
                'favourite_service' => $column->label(__('Serviciu')),
                'total_spent'      => $column->label(__('Status')),
                default            => $column,
            };
        }, $table->getColumns());

        $columns[] = TextColumn::make('actions_placeholder')
            ->label(__('Acțiuni'))
            ->state(static fn () => __('În curând'));

        return $table
            ->columns($columns)
            ->actions([]);
    }
}
