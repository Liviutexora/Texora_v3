<?php

namespace App\Filament\Resources\LoginActivities\Pages;

use App\Filament\Resources\LoginActivities\LoginActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLoginActivity extends EditRecord
{
    protected static string $resource = LoginActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
