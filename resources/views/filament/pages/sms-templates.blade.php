<x-filament-panels::page>
    <form wire:submit="save" novalidate>
        {{ $this->form }}

        <div style="margin-top: 3rem; margin-bottom: 1.5rem;" class="flex items-center justify-end gap-x-3 rounded-xl border border-gray-200 bg-white px-6 py-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <x-filament::button type="submit" size="lg" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ __('Save Templates') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
