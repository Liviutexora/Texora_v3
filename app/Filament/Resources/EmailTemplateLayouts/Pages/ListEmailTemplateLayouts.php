<?php

namespace App\Filament\Resources\EmailTemplateLayouts\Pages;

use App\Filament\Resources\EmailTemplateLayouts\EmailTemplateLayoutResource;
use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplateLayouts extends ListRecords
{
    protected static string $resource = EmailTemplateLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('email_templates')
                ->label(__('Email Templates'))
                ->icon('heroicon-o-envelope-open')
                ->color('gray')
                ->url(EmailTemplateResource::getUrl('index'))
                ->button(),
        ];
    }
}
