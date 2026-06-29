<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Reminder clienți') }}</x-slot>
        <x-slot name="description">{{ __('Lista clienților care trebuie contactați prin reminder.') }}</x-slot>

        @livewire(\App\Filament\Tenant\Widgets\CustomerReturnsTableWidget::class)
    </x-filament::section>
</x-filament-panels::page>
