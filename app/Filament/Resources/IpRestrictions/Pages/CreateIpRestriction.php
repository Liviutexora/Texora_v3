<?php

namespace App\Filament\Resources\IpRestrictions\Pages;

use App\Filament\Resources\IpRestrictions\IpRestrictionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIpRestriction extends CreateRecord
{
    protected static string $resource = IpRestrictionResource::class;
}
