<?php

namespace App\Filament\Resources\IpRestrictions\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\IpRestrictions\IpRestrictionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIpRestriction extends EditRecord
{
    protected static string $resource = IpRestrictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
