<?php

namespace App\Filament\Tenant\Resources\ProviderResource\Pages;

use App\Filament\Tenant\Resources\ProviderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProvider extends EditRecord
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
