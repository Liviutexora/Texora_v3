<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('Quick Actions') }}</x-slot>

        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
            @foreach($actions as $action)
                @if($action['url'])
                @php
                    $styles = match($action['color']) {
                        'violet' => 'background:rgba(139,92,246,0.1);color:#6d28d9;border:1px solid rgba(139,92,246,0.25);',
                        'blue'   => 'background:rgba(37,99,235,0.08);color:#2563eb;border:1px solid rgba(37,99,235,0.2);',
                        'green'  => 'background:rgba(5,150,105,0.08);color:#059669;border:1px solid rgba(5,150,105,0.2);',
                        default  => 'background:rgba(107,114,128,0.08);color:#374151;border:1px solid rgba(107,114,128,0.2);',
                    };
                @endphp
                <a
                    href="{{ $action['url'] }}"
                    @if($action['external']) target="_blank" rel="noopener" @endif
                    style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:opacity 0.15s;{{ $styles }}"
                    onmouseover="this.style.opacity='0.75'" onmouseout="this.style.opacity='1'"
                >
                    <x-filament::icon :icon="$action['icon']" style="width:16px;height:16px;flex-shrink:0;" />
                    {{ $action['label'] }}
                </a>
                @endif
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
