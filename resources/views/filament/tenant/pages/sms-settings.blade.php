<x-filament-panels::page>

    <form wire:submit="save">

        {{ $this->form }}

        <div class="h-8"></div>
        <div class="flex justify-start gap-3">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                {{ __('Save Changes') }}
            </x-filament::button>
        </div>

    </form>

</x-filament-panels::page>
