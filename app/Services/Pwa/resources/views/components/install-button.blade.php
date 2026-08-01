<button
    type="button"
    @if ($id !== '') id="{{ $id }}" @endif
    data-pwa-install-button
    hidden
    class="{{ trim('rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 ' . $class) }}"
>
    {{ $label }}
</button>
<p data-pwa-install-hint hidden class="mt-2 max-w-xs text-xs text-slate-500"></p>