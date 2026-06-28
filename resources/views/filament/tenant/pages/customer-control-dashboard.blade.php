<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ __('Modul CRM în curs de implementare.') }}
        </p>
    </x-filament::section>

    @livewire(\App\Filament\Tenant\Widgets\CrmStatsOverview::class)
</x-filament-panels::page>
