<?php

namespace App\Filament\Tenant\Resources\ProviderResource\Pages;

use App\Filament\Tenant\Resources\ProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProviders extends ListRecords
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
