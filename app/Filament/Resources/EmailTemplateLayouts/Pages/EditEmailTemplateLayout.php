<?php

namespace App\Filament\Resources\EmailTemplateLayouts\Pages;

use App\Filament\Resources\EmailTemplateLayouts\EmailTemplateLayoutResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailTemplateLayout extends EditRecord
{
    protected static string $resource = EmailTemplateLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
