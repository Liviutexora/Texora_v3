<x-filament-panels::page>
    {{ $this->form }}

    <x-filament::button wire:click="save" class="mt-4">
        {{ __('Save Preferences') }}
    </x-filament::button>
</x-filament-panels::page>
