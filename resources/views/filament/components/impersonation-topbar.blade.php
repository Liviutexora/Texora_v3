@php
    $tenantId = session('impersonate_tenant_id');
    $tenant   = $tenantId ? \App\Models\Tenant::find($tenantId) : null;
    $name     = $tenant?->name ?? 'Unknown Business';
@endphp

@if($tenantId)
<div style="background:linear-gradient(90deg,#1e1b4b 0%,#312e81 100%);border-bottom:2px solid #6366f1;display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;padding:9px 20px;box-sizing:border-box;">
    <div style="display:flex;align-items:center;gap:10px;min-width:0;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
             stroke="#a5b4fc" style="width:16px;height:16px;flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>

        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;background:rgba(99,102,241,0.3);border:1px solid rgba(99,102,241,0.6);border-radius:999px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#c7d2fe;white-space:nowrap;flex-shrink:0;">
            {{ __('Managing') }}
        </span>

        <span style="font-size:13px;font-weight:500;color:#e0e7ff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            {{ __('Viewing as') }} <strong style="color:#ffffff;font-weight:700;">{{ $name }}</strong>
        </span>
    </div>

    <a href="{{ route('impersonate.exit') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);border-radius:8px;font-size:12px;font-weight:600;color:#fca5a5;text-decoration:none;white-space:nowrap;flex-shrink:0;transition:background 0.15s;"
       onmouseover="this.style.background='rgba(239,68,68,0.3)'" onmouseout="this.style.background='rgba(239,68,68,0.15)'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
        {{ __('Exit Business') }}
    </a>
</div>
@endif
