<?php

namespace App\Filament\Resources\ContactUs;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\ContactUs\Pages\EditContactUs;
use App\Filament\Resources\ContactUs\Pages\ListContactUs;
use App\Jobs\SendContactUsReplyEmail;
use App\Models\ContactUs;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactUsResource extends Resource
{
    use HasResourcePermissions;

    /**
     * Shield / panel: super_admin should always manage inbox even if permissions were not generated.
     */
    protected static function bypassPermissionChecks(): bool
    {
        $user = static::getAuthUser();

        return $user && method_exists($user, 'hasRole') && $user->hasRole(config('filament-shield.super_admin.name', 'super_admin'));
    }

    public static function canViewAny(): bool
    {
        return static::bypassPermissionChecks() || static::checkPermission('ViewAny', static::$model);
    }

    public static function canView($record): bool
    {
        return static::bypassPermissionChecks() || static::checkPermission('View', $record);
    }

    public static function canCreate(): bool
    {
        return false; // Contact Us is an inbox — entries come from the public form only
    }

    public static function canEdit($record): bool
    {
        return static::bypassPermissionChecks() || static::checkPermission('Update', $record);
    }

    public static function canDelete($record): bool
    {
        return static::bypassPermissionChecks() || static::checkPermission('Delete', $record);
    }

    public static function canDeleteAny(): bool
    {
        return static::bypassPermissionChecks() || static::checkPermission('DeleteAny', static::$model);
    }

    protected static ?string $model = ContactUs::class;

    protected static ?string $modelLabel = 'Contact Message';

    protected static ?string $pluralModelLabel = 'Inbox / Messages';

    protected static ?string $heading = 'Inbox / Messages';

    protected static ?string $slug = 'contact-us';

    protected static ?int $navigationSort = 100;

    protected static ?string $navigationLabel = 'Inbox / Messages';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', ContactUs::STATUS_NEW)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Contact Details'))->schema([
                TextInput::make('name')->label(__('Name'))->disabled()->dehydrated(false),
                TextInput::make('email')->label(__('Email'))->disabled()->dehydrated(false),
                TextInput::make('phone')->label(__('Phone'))->disabled()->dehydrated(false),
                Select::make('type')
                    ->label(__('Inquiry Type'))
                    ->options(ContactUs::TYPE_LIST)
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder(__('—')),
                Textarea::make('message')->rows(6)->disabled()->dehydrated(false)->columnSpanFull(),
                Select::make('status')
                    ->label(__('Status'))
                    ->options(ContactUs::STATUS_LIST)
                    ->default(ContactUs::STATUS_NEW)
                    ->required(),
            ])->columns(2)->columnSpanFull(),

            Section::make(__('Admin Reply'))
                ->schema([
                    Textarea::make('admin_reply')
                        ->label(__('Reply (sent to customer)'))
                        ->rows(5)
                        ->placeholder(__('Write your reply here...'))
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull()
                        ->visible(fn ($record) => $record && $record->admin_reply),
                    TextInput::make('replied_at')
                        ->label(__('Replied At'))
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn ($record) => $record && $record->replied_at)
                        ->formatStateUsing(fn ($record) => $record?->replied_at?->format('d M Y, H:i')),
                ])
                ->visible(fn ($record) => $record && $record->admin_reply)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('email')->label(__('Email'))->searchable()->sortable(),
                TextColumn::make('phone')->label(__('Phone'))->sortable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color('gray')
                    ->placeholder(__('—')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ContactUs::STATUS_NEW         => 'primary',
                        ContactUs::STATUS_IN_PROGRESS => 'warning',
                        ContactUs::STATUS_RESOLVED    => 'success',
                        default                       => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')->dateTime()->label(__('Created')),
                IconColumn::make('admin_reply')
                    ->label(__('Replied'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn ($record) => (bool) $record->admin_reply),
            ])
            ->filters([
                SelectFilter::make('status')->options(ContactUs::STATUS_LIST),
            ])
            ->actions([
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
                            ->default(fn ($record) => $record->admin_reply),
                    ])
                    ->action(function (ContactUs $record, array $data) {
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
                    })
                    ->visible(fn ($record) => (bool) $record->email),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_in_progress')
                        ->label(__('Mark as In Progress'))
                        ->icon('heroicon-o-arrow-right')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => ContactUs::STATUS_IN_PROGRESS]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('mark_resolved')
                        ->label(__('Mark as Resolved'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => ContactUs::STATUS_RESOLVED]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactUs::route('/'),
            'edit'  => EditContactUs::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Communication');
    }

    public static function getModelLabel(): string
    {
        return __('Contact Message');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Inbox / Messages');
    }

    public static function getNavigationLabel(): string
    {
        return __('Inbox / Messages');
    }
}
