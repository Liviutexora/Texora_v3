<?php

namespace App\Filament\Resources\EmailTemplateLayouts;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

use Filament\Actions\Action;

use App\Filament\Resources\EmailTemplateLayouts\Pages\ListEmailTemplateLayouts;
use App\Filament\Concerns\HasResourcePermissions;
use App\Models\EmailTemplateLayout;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class EmailTemplateLayoutResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = EmailTemplateLayout::class;

    protected static ?int $navigationSort = 105;
    protected static ?string $navigationLabel = 'Email Layouts';
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hide from sidebar navigation
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Layout Info'))->schema([
                TextInput::make('name')
                    ->label(__('Layout Name'))
                    ->disabled(),

                Textarea::make('body')
                    ->label(__('Layout Structure'))
                    ->disabled()
                    ->helperText(__('Layout must contain {{BODY}} placeholder')),

                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->disabled(),
            ])->columns(1)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Layout Name'))
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime(),
            ])
            ->actions([
                Action::make('preview')
                    ->label(__('Preview'))
                    ->url(fn (EmailTemplateLayout $record) => route('email-layouts.preview', $record))
                    ->openUrlInNewTab(),

                Action::make('activate')
                    ->label(__('Activate'))
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (EmailTemplateLayout $record) => ! $record->is_active)
                    ->action(function (EmailTemplateLayout $record): void {
                        // Use transaction to ensure data consistency
                        \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                            // Deactivate all layouts
                            EmailTemplateLayout::query()->update(['is_active' => false]);
                            // Activate the selected layout
                            $record->update(['is_active' => true]);
                        });
                    })
                    ->after(fn () => redirect(request()->header('Referer'))), // Refresh the table after action
            ])
            ->bulkActions([]) // disable bulk actions
            ->defaultSort('updated_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailTemplateLayouts::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Communication');
    }

    public static function getNavigationLabel(): string
    {
        return __('Email Layouts');
    }
}
