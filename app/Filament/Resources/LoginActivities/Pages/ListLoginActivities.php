<?php

namespace App\Filament\Resources\LoginActivities\Pages;

use App\Filament\Resources\LoginActivities\LoginActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;

class ListLoginActivities extends ListRecords
{
    protected static string $resource = LoginActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
