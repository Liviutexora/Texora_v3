<?php

namespace App\Filament\Resources\EmailTemplates;

use App\Filament\Resources\EmailTemplates\Pages\EditEmailTemplate;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use App\Filament\Concerns\HasResourcePermissions;
use Filament\Resources\Resource;
use Filament\Tables\Table;

use App\Models\EmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Filament\Resources\EmailTemplates\Pages\ViewEmailTemplate;
use Filament\Actions\Action;
use App\Models\EmailTemplateLayout;
use Filament\Actions\EditAction;

class EmailTemplateResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = EmailTemplate::class;

    protected static ?int $navigationSort = 110;
    protected static ?string $navigationLabel = 'Email Templates';
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope-open';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Basic Info'))->schema([
                TextInput::make('name')->label(__('Template Name'))->disabled(),
                TextInput::make('slug')->label(__('Slug'))->disabled(),
                TextInput::make('subject')
                    ->label(__('Email Subject'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('placeholders')->label(__('Placeholders'))->disabled(),
            ])->columnSpanFull(),

            Section::make(__('Body'))->schema([
                RichEditor::make('body')->label(__('Template Body')),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Template Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->copyable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('Active'))
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                
                Action::make('preview')
                ->label(__('Preview'))
                ->icon('heroicon-o-eye')
                ->url(fn (EmailTemplate $record) => route('email-template.preview', $record))
                ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('Activate Selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label(__('Deactivate Selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null); // disable default row click
    }


    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailTemplates::route('/'),
            'view'  => ViewEmailTemplate::route('/{record}'),
            'edit' => EditEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Email Template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Email Templates');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Communication');
    }

    public static function getNavigationLabel(): string
    {
        return __('Email Templates');
    }
}
