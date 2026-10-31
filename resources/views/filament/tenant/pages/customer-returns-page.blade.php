<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Customer reminders') }}</x-slot>
        <x-slot name="description">{{ __('List of customers who need to be contacted for a follow-up.') }}</x-slot>

        @livewire(\App\Filament\Tenant\Widgets\CustomerReturnsTableWidget::class)
    </x-filament::section>
</x-filament-panels::page>
