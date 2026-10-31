<x-filament-panels::page>
    <div class="space-y-8">
        <section class="space-y-4">
            <div class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-sm">{{ __('FINANCIAL RESULTS') }}</div>
            @livewire(\App\Filament\Tenant\Widgets\CrmFinancialStatsOverview::class)
        </section>

        <section class="space-y-4">
            <div class="rounded-xl bg-violet-600 px-5 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-sm">{{ __('NECESSARY ACTIONS') }}</div>
            @livewire(\App\Filament\Tenant\Widgets\CrmStatsOverview::class)
        </section>
    </div>

    @if($activeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4" wire:click.self="closeModal">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900" role="dialog" aria-modal="true">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                        @if($activeModal === 'customer-status') {{ __('Customer status') }} @elseif($activeModal === 'campaigns') {{ __('Campaigns') }} @else {{ __('Customer reviews') }} @endif
                    </h2>
                    <button type="button" wire:click="closeModal" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Close') }}">×</button>
                </div>

                @if($activeModal === 'customer-status')
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach([__('Active customers'), __('Inactive customers'), __('New customers'), __('Return rate')] as $label)
                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800"><p class="text-sm font-medium text-gray-950 dark:text-white">{{ $label }}</p><p class="mt-2 text-sm text-gray-500">{{ __('In development') }}</p></div>
                        @endforeach
                    </div>
                @elseif($activeModal === 'campaigns')
                    <div class="mt-6 space-y-3">
                        @foreach([__('Customer reactivation'), __('Birthdays'), __('Promotional campaigns'), __('Return campaigns')] as $label)
                            <div class="flex items-center justify-between rounded-xl bg-gray-50 p-4 dark:bg-gray-800"><span class="text-sm font-medium text-gray-950 dark:text-white">{{ $label }}</span><span class="text-sm text-gray-500">{{ __('In development') }}</span></div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-6 text-sm text-gray-500">{{ __('In development') }}</p>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
