<?php

namespace App\Filament\Resources\IpRestrictions\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\IpRestrictions\IpRestrictionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIpRestrictions extends ListRecords
{
    protected static string $resource = IpRestrictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
