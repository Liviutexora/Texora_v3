<?php

namespace App\Filament\Resources\ContactUs\Pages;

use App\Filament\Resources\ContactUs\ContactUsResource;
use App\Jobs\SendContactUsReplyEmail;
use App\Models\ContactUs;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditContactUs extends EditRecord
{
    protected static string $resource = ContactUsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label(__('Reply'))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->modalHeading(__('Reply to Customer'))
                ->modalSubmitActionLabel('Send Reply')
                ->form([
                    Textarea::make('admin_reply')
                        ->label(__('Your Reply'))
                        ->rows(6)
                        ->required()
                        ->placeholder(__('Write your reply here...'))
                        ->default(fn () => $this->getRecord()->admin_reply),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();

                    $record->update([
                        'admin_reply' => $data['admin_reply'],
                        'replied_at'  => now(),
                        'replied_by'  => auth()->id(),
                        'status'      => ContactUs::STATUS_RESOLVED,
                    ]);

                    SendContactUsReplyEmail::dispatchSync($record->fresh());

                    Notification::make()
                        ->title(__('Reply sent successfully'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_reply', 'replied_at']);
                })
                ->visible(fn () => (bool) $this->getRecord()?->email),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Map custom_fields to custom_fields_display for the Repeater
        if (isset($data['custom_fields']) && !empty($data['custom_fields'])) {
            $data['custom_fields_display'] = $data['custom_fields'];
        } else {
            $data['custom_fields_display'] = [];
        }
        
        return $data;
    }
}
