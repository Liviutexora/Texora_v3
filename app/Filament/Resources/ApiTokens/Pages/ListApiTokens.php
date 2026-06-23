<?php

namespace App\Filament\Resources\ApiTokens\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ApiTokens\ApiTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApiTokens extends ListRecords
{
    protected static string $resource = ApiTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
