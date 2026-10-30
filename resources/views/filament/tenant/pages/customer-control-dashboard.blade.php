<x-filament-panels::page>
    <div class="space-y-8">
        <section class="space-y-4">
            <div class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-sm">{{ __('FINANCIAL RESULTS') }}</div>
            <div class="grid gap-5 md:grid-cols-3">
                <button type="button" wire:click="openModal('returns')" class="fi-wi-stats-overview-stat group rounded-xl bg-white p-6 text-left shadow-sm ring-1 ring-gray-950/5 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900 dark:ring-white/10"><x-heroicon-m-banknotes class="mb-4 text-emerald-600" style="width:20px;height:20px" /><p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('Revenue from returning clients') }}</p><p class="mt-2 text-sm text-gray-500">{{ __('In development') }}</p></button>
                <button type="button" wire:click="openModal('future-revenue')" class="fi-wi-stats-overview-stat group rounded-xl bg-white p-6 text-left shadow-sm ring-1 ring-gray-950/5 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900 dark:ring-white/10"><x-heroicon-m-chart-bar class="mb-4 text-emerald-600" style="width:20px;height:20px" /><p class="mb-1 text-sm font-medium text-gray-950 dark:text-white">{{ __('Potential future revenue') }}</p><p class="text-sm text-gray-500">{{ __('In development') }}</p><p class="mt-5 text-xs font-medium text-gray-500">{{ __('Value added this month') }}</p><p class="mt-1 text-sm text-gray-500">{{ __('In development') }}</p></button>
                <button type="button" wire:click="openModal('customer-status')" class="fi-wi-stats-overview-stat group rounded-xl bg-white p-6 text-left shadow-md ring-1 ring-emerald-200 transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-gray-900 dark:ring-emerald-900"><x-heroicon-m-users class="mb-4 text-emerald-600" style="width:20px;height:20px" /><p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('Customer status') }}</p><p class="mt-2 text-sm text-gray-500">{{ __('In development') }}</p></button>
            </div>
        </section>
        <section class="space-y-4">
            <div class="rounded-xl bg-violet-600 px-5 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-sm">{{ __('NECESSARY ACTIONS') }}</div>
            <div class="grid gap-5 md:grid-cols-3">
                <div class="min-w-0 rounded-xl bg-white p-0 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 [&_.fi-wi-stats-overview-stat]:rounded-xl [&_.fi-wi-stats-overview-stat]:shadow-none">@livewire(\App\Filament\Tenant\Widgets\CrmStatsOverview::class)</div>
                <button type="button" wire:click="openModal('campaigns')" class="fi-wi-stats-overview-stat group rounded-xl bg-white p-6 text-left shadow-sm ring-1 ring-gray-950/5 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900 dark:ring-white/10"><x-heroicon-m-megaphone class="mb-4 text-violet-600" style="width:20px;height:20px" /><p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('Campaigns') }}</p><p class="mt-2 text-sm text-gray-500">{{ __('In development') }}</p></button>
                <button type="button" wire:click="openModal('reviews')" class="fi-wi-stats-overview-stat group rounded-xl bg-white p-6 text-left shadow-sm ring-1 ring-gray-950/5 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900 dark:ring-white/10"><x-heroicon-m-star class="mb-4 text-violet-600" style="width:20px;height:20px" /><p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('Customer reviews') }}</p><p class="mt-2 text-sm text-gray-500">{{ __('In development') }}</p></button>
            </div>
        </section>
    </div>
    @if($activeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4" wire:click.self="closeModal"><div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900" role="dialog" aria-modal="true">
            <div class="flex items-center justify-between gap-4"><h2 class="text-lg font-semibold text-gray-950 dark:text-white">@if($activeModal === 'customer-status') {{ __('Customer status') }} @elseif($activeModal === 'campaigns') {{ __('Campaigns') }} @else {{ __('Customer reviews') }} @endif</h2><button type="button" wire:click="closeModal" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="{{ __('Close') }}">×</button></div>
            @if($activeModal === 'customer-status')<div class="mt-6 grid gap-4 sm:grid-cols-2">@foreach([__('Active customers'), __('Inactive customers'), __('New customers'), __('Return rate')] as $label)<div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800"><p class="text-sm font-medium text-gray-950 dark:text-white">{{ $label }}</p><p class="mt-2 text-sm text-gray-500">{{ __('In development') }}</p></div>@endforeach</div>
            @elseif($activeModal === 'campaigns')<div class="mt-6 space-y-3">@foreach([__('Customer reactivation'), __('Birthdays'), __('Promotional campaigns'), __('Return campaigns')] as $label)<div class="flex items-center justify-between rounded-xl bg-gray-50 p-4 dark:bg-gray-800"><span class="text-sm font-medium text-gray-950 dark:text-white">{{ $label }}</span><span class="text-sm text-gray-500">{{ __('In development') }}</span></div>@endforeach</div>
            @else<p class="mt-6 text-sm text-gray-500">{{ __('In development') }}</p>@endif
        </div></div>
    @endif
</x-filament-panels::page>
