<?php

namespace App\Filament\Resources\ApiTokens\Pages;

use App\Filament\Resources\ApiTokens\ApiTokenResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateApiToken extends CreateRecord
{
    protected static string $resource = ApiTokenResource::class;

    protected function afterCreate(): void
    {
        $plain = $this->record->plainTextToken;

        if ($plain) {
            Notification::make()
                ->title(__('API Token Created — Copy it now'))
                ->body("Your token: {$plain}\n\nThis is the only time it will be shown. Store it securely.")
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
