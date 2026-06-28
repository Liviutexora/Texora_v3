<?php

namespace App\Filament\Tenant\Widgets;

use App\Filament\Tenant\Resources\ClientResource;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CustomerReturnsTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = null;

    public function table(Table $table): Table
    {
        // Reuse the exact same Table Builder used by Clients page.
        return ClientResource::table($table);
    }
}
