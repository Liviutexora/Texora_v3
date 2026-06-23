<?php

namespace App\Filament\Resources\EmailTemplateLayouts\Pages;

use App\Filament\Resources\EmailTemplateLayouts\EmailTemplateLayoutResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmailTemplateLayout extends ViewRecord
{
    protected static string $resource = EmailTemplateLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
