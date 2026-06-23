<?php

namespace App\Filament\Tenant\Resources\ProviderResource\RelationManagers;

use App\Enums\SlotOverrideStatus;
use App\Models\ProviderSlotOverride;
use App\Support\TenantContext;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlockedDatesRelationManager extends RelationManager
{
    protected static string $relationship = 'slotOverrides';

    protected static ?string $title = 'Blocked Dates / Overrides';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                DatePicker::make('date')
                    ->required()
                    ->minDate(now())
                    ->label(__('Date')),

                Select::make('status')
                    ->options(SlotOverrideStatus::options())
                    ->default(SlotOverrideStatus::Blocked->value)
                    ->required(),

                TimePicker::make('start_time')
                    ->label(__('Block from (leave blank = all day)'))
                    ->seconds(false),

                TimePicker::make('end_time')
                    ->label(__('Block until'))
                    ->seconds(false)
                    ->after('start_time'),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')->date('d M Y')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (SlotOverrideStatus $state) => match ($state) {
                        SlotOverrideStatus::Blocked   => 'danger',
                        SlotOverrideStatus::Reserved  => 'warning',
                    }),
                TextColumn::make('start_time')->label(__('From'))->time('H:i')->placeholder(__('All day')),
                TextColumn::make('end_time')->label(__('Until'))->time('H:i')->placeholder(__('—')),
            ])
            ->defaultSort('date')
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['tenant_id'] = TenantContext::id();
                        return $data;
                    }),

                // Block a date range for this provider
                Action::make('blockRange')
                    ->label(__('Block Date Range'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('from')->required()->minDate(now()),
                            DatePicker::make('to')->required()->minDate(now())->afterOrEqual('from'),
                        ]),
                    ])
                    ->action(function (array $data): void {
                        $from     = \Carbon\Carbon::parse($data['from']);
                        $to       = \Carbon\Carbon::parse($data['to']);
                        $providerId = $this->getOwnerRecord()->id;
                        $tenantId = TenantContext::id();

                        $current = $from->copy();
                        while ($current->lte($to)) {
                            ProviderSlotOverride::withoutGlobalScope('tenant')->firstOrCreate(
                                ['tenant_id' => $tenantId, 'provider_id' => $providerId, 'date' => $current->toDateString(), 'start_time' => null, 'end_time' => null],
                                ['status' => SlotOverrideStatus::Blocked->value],
                            );
                            $current->addDay();
                        }

                        \Filament\Notifications\Notification::make()
                            ->title(__('Date range blocked'))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
