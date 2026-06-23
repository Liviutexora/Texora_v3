<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Filament\Resources\EmailTemplateLayouts\EmailTemplateLayoutResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('email_layouts')
                ->label(__('Email Layouts'))
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->url(EmailTemplateLayoutResource::getUrl('index'))
                ->button(),
            // CreateAction::make(),
        ];
    }
}
