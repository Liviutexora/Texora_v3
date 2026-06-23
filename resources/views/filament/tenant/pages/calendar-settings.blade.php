<x-filament-panels::page>

@php
    $oauthOk = $data['oauth_configured'] ?? false;
@endphp

{{-- ── Flash messages ───────────────────────────────────────────────────── --}}
@if (session('success'))
<div class="cs-alert cs-alert--success">
    <svg class="cs-alert__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span class="cs-alert__msg cs-alert__msg--success">{{ session('success') }}</span>
</div>
@endif

@if (session('error'))
<div class="cs-alert cs-alert--error">
    <svg class="cs-alert__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span class="cs-alert__msg cs-alert__msg--error">{{ session('error') }}</span>
</div>
@endif

{{-- ── OAuth not configured warning ─────────────────────────────────────── --}}
@if (! $oauthOk)
<div class="cs-oauth-warning">
    <div class="cs-oauth-warning__icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div>
        <div class="cs-oauth-warning__title">{{ __('Google OAuth credentials not configured') }}</div>
        <div class="cs-oauth-warning__body">{{ __('Google Calendar sync is unavailable until an admin adds OAuth credentials.') }}</div>
        <ol class="cs-oauth-steps">
            @foreach([
                __('Go to') . ' <strong>' . __('Admin → Settings → Integrations') . '</strong> ' . __('tab'),
                __('Add your Google OAuth Client ID and Client Secret'),
                __('Return here to connect each provider\'s calendar'),
            ] as $i => $step)
            <li class="cs-oauth-step">
                <span class="cs-oauth-step__num">{{ $i + 1 }}</span>
                <span>{!! $step !!}</span>
            </li>
            @endforeach
        </ol>
    </div>
</div>
@endif

{{-- ── Sync settings form ───────────────────────────────────────────────── --}}
<form wire:submit="save">
    {{ $this->form }}
    <div class="cs-form-actions">
        <x-filament::button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">{{ __('Save Changes') }}</span>
            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
        </x-filament::button>
    </div>
</form>

{{-- ── Provider connections ─────────────────────────────────────────────── --}}
<div class="cs-providers">

    {{-- Section header --}}
    <div class="cs-providers__header">
        <div>
            <div class="cs-providers__title">{{ __('Provider connections') }}</div>
            <div class="cs-providers__subtitle">{{ __('Each provider must connect their own Google account to sync their calendar.') }}</div>
        </div>
        @php
            $connectedCount = isset($providers) ? $providers->filter(fn ($p) => $p->calendar_sync_enabled && $p->google_access_token)->count() : 0;
        @endphp
        @if(isset($providers) && $providers->isNotEmpty())
        <div class="cs-providers__count">
            @if($connectedCount > 0)
                <span class="cs-providers__count-dot"></span>
                <span class="cs-providers__count-label">{{ $connectedCount }} {{ __('connected') }}</span>
                <span class="cs-providers__sep">·</span>
            @endif
            <span>{{ $providers->count() }} {{ Str::plural(__('provider'), $providers->count()) }}</span>
        </div>
        @endif
    </div>

    {{-- Provider list --}}
    @if(!isset($providers) || $providers->isEmpty())
    <div class="cs-providers__empty">
        <svg class="cs-providers__empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <div class="cs-providers__empty-text">{{ __('No providers found. Add providers first.') }}</div>
    </div>
    @else
    <div class="cs-providers__list">
        @foreach ($providers as $provider)
        @php
            $isConnected = $provider->calendar_sync_enabled && $provider->google_access_token;
            $name = $provider->user?->name ?? __('Provider #:id', ['id' => $provider->id]);
            $email = $provider->user?->email ?? null;
            $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
        @endphp
        <div class="cs-provider-row {{ !$loop->last ? 'cs-provider-row--bordered' : '' }}">

            {{-- Avatar + info --}}
            <div class="cs-provider-info">
                <div class="cs-provider-avatar {{ $isConnected ? 'cs-provider-avatar--connected' : 'cs-provider-avatar--disconnected' }}">
                    @if($isConnected)
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        {{ $initials ?: '#' }}
                    @endif
                </div>

                {{-- Name + status --}}
                <div class="cs-provider-meta">
                    <div class="cs-provider-name">{{ $name }}</div>
                    <div class="cs-provider-status">
                        @if($isConnected)
                            <span class="cs-provider-dot cs-provider-dot--connected"></span>
                            <span class="cs-provider-connected-label">{{ __('Connected') }}</span>
                        @else
                            <span class="cs-provider-dot cs-provider-dot--disconnected"></span>
                            <span class="cs-provider-na-label">{{ __('Not connected') }}</span>
                        @endif
                        @if($email)
                            <span class="cs-provider-sep">·</span>
                            <span class="cs-provider-email">{{ $email }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action button --}}
            <div class="cs-provider-actions">
                @if($isConnected)
                <form method="POST" action="{{ route('calendar.oauth.disconnect') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                    <button type="submit" class="cs-btn-disconnect">{{ __('Disconnect') }}</button>
                </form>
                @else
                <a href="{{ $oauthOk ? route('calendar.oauth.redirect', ['provider_id' => $provider->id]) : '#' }}"
                   class="cs-btn-connect {{ !$oauthOk ? 'cs-btn-connect--disabled' : '' }}">
                    <svg class="{{ $oauthOk ? 'cs-google-icon--active' : 'cs-google-icon--disabled' }}" width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    {{ __('Connect Google') }}
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

</x-filament-panels::page>
