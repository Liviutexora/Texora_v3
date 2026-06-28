<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Reveniri clienți') }}</x-slot>
        <x-slot name="description">{{ __('Tabel randat nativ prin aceeași componentă Filament folosită la Clienți.') }}</x-slot>

        @livewire(\App\Filament\Tenant\Widgets\CustomerReturnsTableWidget::class)
    </x-filament::section>
</x-filament-panels::page>
