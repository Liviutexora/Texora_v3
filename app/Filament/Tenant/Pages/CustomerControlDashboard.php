<?php

namespace App\Filament\Tenant\Pages;

use Filament\Pages\Page;
use Livewire\Attributes\On;

class CustomerControlDashboard extends Page
{
    public ?string $activeModal = null;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'panou-control-clienti';

    protected string $view = 'filament.tenant.pages.customer-control-dashboard';

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    public static function getNavigationLabel(): string
    {
        return __('Customer Control Dashboard');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Customer Control Dashboard');
    }

    #[On('open-crm-modal')]
    public function openModal(string $modal): void
    {
        $this->activeModal = $modal;
    }

    public function closeModal(): void
    {
        $this->activeModal = null;
    }
}
