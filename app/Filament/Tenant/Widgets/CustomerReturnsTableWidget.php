<?php

namespace App\Filament\Tenant\Widgets;

use App\Filament\Tenant\Resources\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
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

        return $table
            ->columns($columns)
            ->actionsColumnLabel(__('Acțiuni'))
            ->actions([
                ActionGroup::make([
                    Action::make('send_sms')
                        ->label(__('Trimite SMS'))
                        ->action(static fn () => null),
                    Action::make('send_email')
                        ->label(__('Trimite e-mail'))
                        ->action(static fn () => null),
                    Action::make('mark_called')
                        ->label(__('Marchează ca apelat'))
                        ->action(static fn () => null),
                    Action::make('reschedule')
                        ->label(__('Reprogramează'))
                        ->action(static fn () => null),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->iconButton(),
            ]);
    }
}
