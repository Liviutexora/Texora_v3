<?php

namespace App\Filament\Tenant\Pages;

use Filament\Pages\Page;

class FormBuilderPage extends Page
{
    protected static string|\BackedEnum|null  $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string                  $navigationLabel = null;
    protected static ?string                  $title           = null;
    protected string                          $view            = 'filament.tenant.pages.form-builder';
    protected static string|\UnitEnum|null    $navigationGroup = 'Operations';
    public static function getNavigationGroup(): ?string { return __('Operations'); }
    protected static ?int                     $navigationSort  = 5;

    public static function getNavigationLabel(): string
    {
        return __('Form Builder');
    }

    public function getTitle(): string
    {
        return '';   // title rendered inside the Livewire component
    }

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }
}
