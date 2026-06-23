<?php

namespace App\Filament\Resources\ApiTokens\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ApiTokens\ApiTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApiToken extends EditRecord
{
    protected static string $resource = ApiTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
