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

    /**
     * UI-only status state keyed by record id.
     *
     * @var array<string, array{label: string, at: string}>
     */
    public array $statusByRecord = [];

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
                'total_spent'      => $column
                    ->label(__('Status'))
                    ->state(fn ($record): string => $this->statusByRecord[$this->getRecordKey($record)]['label'] ?? __('Necontactat'))
                    ->description(fn ($record): ?string => $this->statusByRecord[$this->getRecordKey($record)]['at'] ?? null),
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
                        ->action(function ($record): void {
                            $this->setStatus($record, __('SMS trimis'));
                        }),
                    Action::make('send_email')
                        ->label(__('Trimite e-mail'))
                        ->action(function ($record): void {
                            $this->setStatus($record, __('E-mail trimis'));
                        }),
                    Action::make('mark_called')
                        ->label(__('Marchează ca apelat'))
                        ->action(function ($record): void {
                            $this->setStatus($record, __('Apelat'));
                        }),
                    Action::make('reschedule')
                        ->label(__('Reprogramează'))
                        ->action(static fn () => null),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->iconButton(),
            ]);
    }

    private function setStatus($record, string $label): void
    {
        $this->statusByRecord[$this->getRecordKey($record)] = [
            'label' => $label,
            'at' => now()->format('d.m.Y H:i'),
        ];
    }

    private function getRecordKey($record): string
    {
        if (is_object($record)) {
            if (method_exists($record, 'getKey')) {
                return (string) $record->getKey();
            }

            if (isset($record->id)) {
                return (string) $record->id;
            }
        }

        return (string) $record;
    }
}
