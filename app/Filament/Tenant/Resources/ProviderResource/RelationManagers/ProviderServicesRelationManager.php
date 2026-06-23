<?php

namespace App\Filament\Tenant\Resources\ProviderResource\RelationManagers;

use App\Models\Service;
use App\Support\TenantContext;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProviderServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $title = 'Services Offered';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('duration_minutes')->label(__('Duration'))->suffix(' min'),
                TextColumn::make('price')->money(fn ($record) => $record->currency),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelectOptionsQuery(fn ($query) => $query
                        ->withoutGlobalScope('tenant')
                        ->where('tenant_id', TenantContext::id()))
                    ->preloadRecordSelect(),
            ])
            ->actions([DetachAction::make()])
            ->bulkActions([DetachBulkAction::make()]);
    }
}
