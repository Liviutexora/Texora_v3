@php
    $recaptchaEnabled = app(\App\Services\RecaptchaService::class)->isEnabled();
    $recaptchaSiteKey = \App\Models\Setting::get('google_recaptcha_site_key');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Get Started') }} — {{ config('app.name', 'Slotara') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <meta name="description" content="Create your business booking page on {{ config('app.name', 'Slotara') }}.">
    @vite(['resources/css/app.css'])
    @if($recaptchaEnabled && $recaptchaSiteKey)
    <script src="{{ asset('js/vendor/recaptcha-api.js') }}?render={{ $recaptchaSiteKey }}"></script>
    @endif
    <link rel="stylesheet" href="/font-proxy?family=Inter&weights=400;500;600;700;800;900">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin: 0; min-height: 100vh; background: #f5f3ff; }

        /* ── Layout ── */
        .page-wrap { min-height: 100vh; display: flex; flex-direction: column; }
        .page-nav {
            width: 100%; max-width: 1100px; margin: 0 auto;
            padding: 20px 24px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .page-main {
            flex: 1; display: flex; align-items: flex-start;
            justify-content: center; padding: 12px 24px 60px;
        }

        /* ── Card shell ── */
        .signup-card {
            width: 100%; max-width: 960px;
            display: grid; grid-template-columns: 1fr 1fr;
            border-radius: 24px; overflow: hidden;
            box-shadow: 0 24px 80px rgba(109, 40, 217, 0.18), 0 4px 24px rgba(0,0,0,0.08);
        }
        @media (max-width: 720px) {
            .signup-card { grid-template-columns: 1fr; }
            .panel-left  { display: none; }
        }

        /* ── Left panel ── */
        .panel-left {
            background: linear-gradient(150deg, #4c1d95 0%, #5b21b6 35%, #6d28d9 65%, #7c3aed 100%);
            padding: 40px 40px 48px;
            display: flex; flex-direction: column;
            position: relative; overflow: hidden;
        }
        .panel-left::before {
            content: ''; position: absolute; top: -80px; right: -80px;
            width: 280px; height: 280px; background: rgba(255,255,255,0.06); border-radius: 50%;
        }
        .panel-left::after {
            content: ''; position: absolute; bottom: -60px; left: -60px;
            width: 220px; height: 220px; background: rgba(255,255,255,0.04); border-radius: 50%;
        }

        /* ── Plan tabs ── */
        .plan-tabs {
            display: flex; gap: 6px; flex-wrap: wrap;
            margin-bottom: 16px; position: relative; z-index: 1;
        }

        /* ── Billing cycle toggle ── */
        #cycle-toggle { margin-bottom: 16px; position: relative; z-index: 1; }
        .cycle-toggle-inner { display: flex; gap: 4px; background: rgba(255,255,255,0.12); border-radius: 9999px; padding: 4px; width: fit-content; }
        .cycle-btn { padding: 6px 14px; border-radius: 9999px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; color: rgba(255,255,255,0.65); background: transparent; font-family: 'Inter', sans-serif; white-space: nowrap; }
        .cycle-btn.active { background: white; color: #5b21b6; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .cycle-btn:hover:not(.active) { background: rgba(255,255,255,0.2); color: white; }
        .plan-tab {
            padding: 7px 16px; border-radius: 9999px;
            font-size: 13px; font-weight: 600; border: none; cursor: pointer;
            transition: all 0.2s; color: rgba(255,255,255,0.65);
            background: rgba(255,255,255,0.12);
            white-space: nowrap;
        }
        .plan-tab.active {
            background: white; color: #5b21b6;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .plan-tab:hover:not(.active) { background: rgba(255,255,255,0.2); color: white; }

        /* Price display */
        .price-block { position: relative; z-index: 1; margin-bottom: 8px; }
        .price-label { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; color: rgba(255,255,255,0.5); text-transform: uppercase; margin-bottom: 6px; }
        .price-main  { display: flex; align-items: flex-end; gap: 4px; }
        .price-big   { font-size: 64px; font-weight: 900; color: white; line-height: 1; letter-spacing: -2px; }
        .price-mo    { font-size: 16px; font-weight: 500; color: rgba(255,255,255,0.6); padding-bottom: 10px; }
        .price-note  { font-size: 13px; color: rgba(255,255,255,0.55); margin-bottom: 32px; min-height: 20px; position: relative; z-index: 1; }

        /* Divider */
        .panel-divider { border: none; border-top: 1px solid rgba(255,255,255,0.15); margin: 0 0 28px; }

        /* Features */
        .feature-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 14px; flex: 1; position: relative; z-index: 1; }
        .feature-item { display: flex; align-items: center; gap: 12px; }
        .feature-icon {
            width: 22px; height: 22px; background: rgba(255,255,255,0.15);
            border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .feature-icon svg { width: 11px; height: 11px; color: white; }
        .feature-text { font-size: 14px; color: rgba(255,255,255,0.85); font-weight: 500; line-height: 1.3; }

        /* Trust row */
        .trust-row {
            display: flex; gap: 16px; flex-wrap: wrap;
            margin-top: 32px; padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.12);
            position: relative; z-index: 1;
        }
        .trust-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: rgba(255,255,255,0.5); font-weight: 500; }
        .trust-item svg { width: 13px; height: 13px; color: rgba(255,255,255,0.4); flex-shrink: 0; }

        /* ── Right panel (form) ── */
        .panel-right { background: white; padding: 48px 44px; display: flex; flex-direction: column; }
        .form-heading { font-size: 22px; font-weight: 800; color: #111827; margin: 0 0 6px; }
        .form-sub     { font-size: 14px; color: #6b7280; margin: 0 0 32px; }

        /* Fields */
        .field-group { display: flex; flex-direction: column; gap: 20px; }
        .field-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 400px) { .field-row { grid-template-columns: 1fr; } }
        .field-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .field-label span { color: #f87171; margin-left: 2px; }
        .field-wrap { position: relative; }
        .field-input {
            width: 100%; padding: 11px 40px 11px 14px;
            border-radius: 10px; border: 1.5px solid #e5e7eb;
            background: #fafafa; font-size: 14px; color: #111827;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s; outline: none;
        }
        .field-input::placeholder { color: #9ca3af; }
        .field-input:focus { border-color: #7c3aed; background: white; box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }
        .field-input.has-error { border-color: #f87171; }
        .field-toggle-btn {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #9ca3af; padding: 2px;
            display: flex; align-items: center;
        }
        .field-toggle-btn:hover { color: #6b7280; }
        .field-error { font-size: 12px; color: #ef4444; margin-top: 5px; }

        /* Errors banner */
        .errors-banner {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #dc2626; border-radius: 10px;
            padding: 12px 16px; font-size: 13px; margin-bottom: 20px;
        }
        .errors-banner ul { margin: 0; padding: 0 0 0 16px; }

        /* Submit */
        .submit-btn {
            width: 100%; margin-top: 28px; padding: 14px 24px;
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: white; font-size: 15px; font-weight: 700;
            border: none; border-radius: 12px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: opacity 0.2s, box-shadow 0.2s, transform 0.1s;
            box-shadow: 0 4px 16px rgba(109,40,217,0.35);
            font-family: 'Inter', sans-serif;
        }
        .submit-btn:hover { box-shadow: 0 6px 28px rgba(109,40,217,0.45); transform: translateY(-1px); }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

        .signin-row { margin-top: 24px; text-align: center; font-size: 13px; color: #9ca3af; }
        .signin-row a { color: #7c3aed; font-weight: 600; text-decoration: none; }
        .signin-row a:hover { text-decoration: underline; }

        .trial-note { margin-top: 12px; text-align: center; font-size: 12px; color: #9ca3af; line-height: 1.5; }
        .trial-note strong { color: #6b7280; }

        /* Nav */
        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo-icon { width: 36px; height: 36px; border-radius: 10px; background: #7c3aed; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(124,58,237,0.3); }
        .nav-logo-text { font-size: 17px; font-weight: 800; color: #111827; letter-spacing: -0.3px; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-link { font-size: 14px; font-weight: 500; color: #6b7280; text-decoration: none; padding: 7px 14px; border-radius: 8px; transition: color 0.15s, background 0.15s; }
        .nav-link:hover { color: #111827; background: rgba(0,0,0,0.04); }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="page-wrap">

    {{-- Nav --}}
    <nav style="position: relative; z-index: 10;">
        <div class="page-nav">
            <a href="/" class="nav-logo">
                <div class="nav-logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:white" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <span class="nav-logo-text">{{ config('app.name', 'Slotara') }}</span>
            </a>
            <div class="nav-links">
                <a href="{{ route('contact') }}" class="nav-link">{{ __('Contact') }}</a>
                <a href="{{ route('login') }}" class="nav-link">{{ __('Sign In') }}</a>
            </div>
        </div>
    </nav>

    {{-- Main --}}
    <div class="page-main">

        @php
            $extractFeatures = function (?array $raw): array {
                if (! $raw) return [];
                $out = [];
                foreach ($raw as $feat) {
                    $text = is_array($feat) ? ($feat['text'] ?? $feat['feature'] ?? '') : (string) $feat;
                    if ($text !== '') $out[] = $text;
                }
                return $out;
            };

            $firstPlan       = $plans->first();
            $firstPlanPrice  = $firstPlan?->activePrices->first();
            $firstPlanIsFree = ! $firstPlanPrice || (float) $firstPlanPrice->price === 0.0;

            $plansJs = $plans->mapWithKeys(function ($p) use ($extractFeatures) {
                $prices = $p->activePrices->map(fn ($pr) => [
                    'billing_cycle'  => $pr->billing_cycle,
                    'price'          => (float) $pr->price,
                    'interval_label' => $pr->intervalLabel(),
                    'interval_short' => $pr->intervalShort(),
                    'cycle_label'    => $pr->cycleLabel(),
                ])->values()->toArray();

                $isFree = empty($prices) || collect($prices)->every(fn ($pr) => $pr['price'] === 0.0);

                return [$p->id => [
                    'id'       => $p->id,
                    'name'     => $p->name,
                    'is_free'  => $isFree,
                    'prices'   => $prices,
                    'features' => $extractFeatures($p->features),
                ]];
            })->all();
        @endphp

        <div class="signup-card">

            {{-- ═══ LEFT — Pricing panel ═══ --}}
            <div class="panel-left">

                {{-- Plan tabs --}}
                @if($plans->count() > 1)
                    <div class="plan-tabs" id="plan-tabs">
                        @foreach($plans as $plan)
                            <button
                                type="button"
                                class="plan-tab {{ $loop->first ? 'active' : '' }}"
                                id="tab-{{ $plan->id }}"
                                onclick="selectPlan({{ $plan->id }})"
                            >{{ $plan->name }}</button>
                        @endforeach
                    </div>
                @endif

                {{-- Billing cycle toggle (populated + shown by JS for paid plans with multiple cycles) --}}
                <div id="cycle-toggle" style="display:none;"></div>

                {{-- Price --}}
                <div class="price-block">
                    <p class="price-label" id="plan-name-label">{{ $firstPlan?->name ?? __('Plan') }}</p>
                    <div class="price-main">
                        <span class="price-big" id="price-amount">
                            @if($firstPlanIsFree)
                                Free
                            @else
                                ${{ (int) ($firstPlanPrice?->price ?? 0) }}
                            @endif
                        </span>
                        @if(! $firstPlanIsFree && $firstPlanPrice)
                            <span class="price-mo" id="price-period">/{{ $firstPlanPrice->intervalLabel() }}</span>
                        @else
                            <span class="price-mo" id="price-period"></span>
                        @endif
                    </div>
                </div>
                <p class="price-note" id="price-note">
                    @if($firstPlanIsFree)
                        {{ __('No credit card required') }}
                    @elseif($firstPlanPrice)
                        {{ __('Billed :cycle · cancel anytime', ['cycle' => strtolower($firstPlanPrice->cycleLabel())]) }}
                    @endif
                </p>

                <hr class="panel-divider">

                {{-- Features --}}
                <ul class="feature-list" id="feature-list">
                    @foreach($extractFeatures($firstPlan?->features) as $text)
                        <li class="feature-item">
                            <div class="feature-icon">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="feature-text">{{ $text }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Trust row --}}
                <div class="trust-row">
                    <div class="trust-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        {{ __('Secured by Stripe') }}
                    </div>
                    <div class="trust-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ __('Cancel anytime') }}
                    </div>
                    <div class="trust-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ __('Instant access') }}
                    </div>
                </div>
            </div>

            {{-- ═══ RIGHT — Form panel ═══ --}}
            <div class="panel-right">

                <h1 class="form-heading">{{ __('Create your account') }}</h1>
                <p class="form-sub" id="form-sub">
                    @if($firstPlanIsFree)
                        {{ __('Free forever. No credit card needed.') }}
                    @else
                        {{ __("You'll be taken to our secure payment page to complete setup.") }}
                    @endif
                </p>

                @if($errors->any())
                    <div class="errors-banner">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('info'))
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:20px;">
                        {{ session('info') }}
                    </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST" id="signup-form">
                    @csrf
                    <input type="hidden" name="plan_id"       id="plan-id-input"       value="{{ $firstPlan?->id }}">
                    <input type="hidden" name="billing_cycle" id="billing-cycle-input" value="{{ $firstPlanPrice?->billing_cycle ?? 'monthly' }}">
                    @if($recaptchaEnabled && $recaptchaSiteKey)
                        <input type="hidden" name="g-recaptcha-response" id="register-recaptcha-response">
                    @endif

                    <div class="field-group">

                        <div>
                            <label class="field-label" for="name">{{ __('Full Name') }} <span>*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                placeholder="{{ __('Jane Smith') }}" required autocomplete="name"
                                class="field-input {{ $errors->has('name') ? 'has-error' : '' }}">
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="field-label" for="email">{{ __('Email Address') }} <span>*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="{{ __('jane@yourbusiness.com') }}" required autocomplete="email"
                                class="field-input {{ $errors->has('email') ? 'has-error' : '' }}">
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field-row">
                            <div>
                                <label class="field-label" for="password">{{ __('Password') }} <span>*</span></label>
                                <div class="field-wrap">
                                    <input type="password" id="password" name="password"
                                        placeholder="{{ __('Min. :min characters', ['min' => $passwordMinLength]) }}" required autocomplete="new-password"
                                        class="field-input {{ $errors->has('password') ? 'has-error' : '' }}">
                                    <button type="button" class="field-toggle-btn" onclick="togglePwd('password')">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    </button>
                                </div>
                                @error('password')<p class="field-error">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="field-label" for="password_confirmation">{{ __('Confirm Password') }} <span>*</span></label>
                                <div class="field-wrap">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        placeholder="{{ __('Repeat password') }}" required autocomplete="new-password"
                                        class="field-input">
                                    <button type="button" class="field-toggle-btn" onclick="togglePwd('password_confirmation')">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="submit-btn" id="submit-btn">
                        <span id="btn-text">
                            @if($firstPlanIsFree)
                                {{ __('Get started free') }}
                            @else
                                {{ __('Continue to payment') }}
                            @endif
                        </span>
                        <svg id="btn-arrow" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        <svg id="btn-spinner" style="display:none;animation:spin 0.75s linear infinite" width="16" height="16" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </button>

                    <p class="trial-note" id="trial-note">
                        @if($firstPlanIsFree)
                            {{ __('Free forever — no credit card required.') }}
                        @elseif($firstPlanPrice)
                            <strong id="cta-price">${{ (int) $firstPlanPrice->price }}/{{ $firstPlanPrice->intervalShort() }}</strong> {{ __('Billed :cycle. Cancel anytime.', ['cycle' => strtolower($firstPlanPrice->cycleLabel())]) }}
                        @endif
                    </p>

                </form>

                <p class="signin-row">{{ __('Already have an account?') }} <a href="{{ route('login') }}">{{ __('Sign In') }}</a></p>

            </div>

        </div>
    </div>
</div>

<script>
    'use strict';
    var FEATURE_ICON = '<div class="feature-icon"><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg></div>';

    // All plan data from DB (includes prices[] array per plan)
    var PLANS = @json($plansJs);

    var currentPlanId = {{ $firstPlan?->id ?? 'null' }};
    var currentCycle  = '{{ $firstPlanPrice?->billing_cycle ?? 'monthly' }}';

    /* ── Helpers ─────────────────────────────────────────────────────── */

    function getSavingsPct(prices) {
        var monthly = prices.find(function(p) { return p.billing_cycle === 'monthly'; });
        var yearly  = prices.find(function(p) { return p.billing_cycle === 'yearly'; });
        if (!monthly || !yearly || monthly.price === 0) return null;
        var pct = Math.round((1 - (yearly.price / 12) / monthly.price) * 100);
        return pct > 0 ? pct : null;
    }

    function updatePriceDisplay(plan, cycle) {
        var priceEntry = plan.prices.find(function(p) { return p.billing_cycle === cycle; });
        if (!priceEntry && plan.prices.length > 0) priceEntry = plan.prices[0];

        var isFree = plan.is_free || !priceEntry || priceEntry.price === 0;

        document.getElementById('plan-name-label').textContent = plan.name;

        if (isFree) {
            document.getElementById('price-amount').textContent = @json(__('Free'));
            document.getElementById('price-period').textContent = '';
            document.getElementById('price-note').textContent   = @json(__('No credit card required'));
        } else {
            document.getElementById('price-amount').textContent = '$' + Math.round(priceEntry.price);
            document.getElementById('price-period').textContent = '/' + priceEntry.interval_label;
            document.getElementById('price-note').textContent   = @json(__('Billed')) + ' ' + priceEntry.cycle_label.toLowerCase() + ' · ' + @json(__('cancel anytime'));
        }

        // Features
        var ul = document.getElementById('feature-list');
        if (plan.features && plan.features.length > 0) {
            ul.innerHTML = plan.features.map(function(text) {
                return '<li class="feature-item">' + FEATURE_ICON + '<span class="feature-text">' + text + '</span></li>';
            }).join('');
        } else {
            ul.innerHTML = '';
        }

        // Right panel
        document.getElementById('form-sub').textContent = isFree
            ? @json(__('Free forever. No credit card needed.'))
            : @json(__("You'll be taken to our secure payment page to complete setup."));

        document.getElementById('btn-text').textContent = isFree ? '{{ __('Get Started') }}' : '{{ __('Continue to payment') }}';

        if (isFree) {
            document.getElementById('trial-note').innerHTML = @json(__('Free forever — no credit card required.'));
        } else {
            var _billedLabels = {
                yearly:  @json(__('billed annually')),
                weekly:  @json(__('billed weekly')),
                monthly: @json(__('billed monthly')),
            };
            var _cancelAnytime = @json(__('Cancel anytime.'));
            document.getElementById('trial-note').innerHTML =
                '<strong>$' + Math.round(priceEntry.price) + '/' + priceEntry.interval_short + '</strong> '
                + (_billedLabels[priceEntry.billing_cycle] || _billedLabels.monthly) + '. ' + _cancelAnytime;
        }
    }

    /* ── Plan selection ──────────────────────────────────────────────── */

    function selectPlan(id) {
        var plan = PLANS[id];
        if (!plan) return;
        currentPlanId = id;

        // Default to first price cycle for this plan
        var defaultCycle = plan.prices.length > 0 ? plan.prices[0].billing_cycle : 'monthly';
        currentCycle = defaultCycle;

        // Build / show cycle toggle for multi-cycle paid plans
        var toggleEl = document.getElementById('cycle-toggle');
        if (!plan.is_free && plan.prices.length > 1) {
            var savingsPct = getSavingsPct(plan.prices);
            var btns = plan.prices.map(function(pr) {
                var label = pr.cycle_label;
                if (pr.billing_cycle === 'yearly' && savingsPct) {
                    label += ' — save ' + savingsPct + '%';
                }
                return '<button type="button" class="cycle-btn' + (pr.billing_cycle === defaultCycle ? ' active' : '') + '" '
                     + 'id="cycle-btn-' + pr.billing_cycle + '" '
                     + 'onclick="selectCycle(\'' + pr.billing_cycle + '\')">' + label + '</button>';
            }).join('');
            toggleEl.innerHTML = '<div class="cycle-toggle-inner">' + btns + '</div>';
            toggleEl.style.display = 'block';
        } else {
            toggleEl.style.display = 'none';
            toggleEl.innerHTML = '';
        }

        // Update hidden inputs
        document.getElementById('plan-id-input').value       = id;
        document.getElementById('billing-cycle-input').value = defaultCycle;

        // Update price / features / right-panel copy
        updatePriceDisplay(plan, defaultCycle);

        // Tab active state
        document.querySelectorAll('.plan-tab').forEach(function(btn) {
            btn.classList.toggle('active', parseInt(btn.id.replace('tab-', '')) === id);
        });
    }

    /* ── Cycle selection ─────────────────────────────────────────────── */

    function selectCycle(cycle) {
        currentCycle = cycle;
        var plan = PLANS[currentPlanId];
        if (!plan) return;

        document.getElementById('billing-cycle-input').value = cycle;

        // Active state on cycle buttons
        document.querySelectorAll('.cycle-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.id === 'cycle-btn-' + cycle);
        });

        updatePriceDisplay(plan, cycle);
    }

    /* ── Misc ─────────────────────────────────────────────────────────── */

    function togglePwd(fieldId) {
        var el = document.getElementById(fieldId);
        el.type = el.type === 'password' ? 'text' : 'password';
    }

    document.getElementById('signup-form').addEventListener('submit', function (e) {
        var btn         = document.getElementById('submit-btn');
        @if($recaptchaEnabled && $recaptchaSiteKey)
        var captchaInput = document.getElementById('register-recaptcha-response');
        if (captchaInput && captchaInput.value === '') {
            e.preventDefault();
            var form = this;
            btn.disabled = true;
            document.getElementById('btn-arrow').style.display   = 'none';
            document.getElementById('btn-spinner').style.display = 'block';
            grecaptcha.execute('{{ $recaptchaSiteKey }}', {action: 'register'}).then(function (token) {
                captchaInput.value = token;
                form.submit();
            });
            return;
        }
        @endif
        btn.disabled = true;
        document.getElementById('btn-arrow').style.display   = 'none';
        document.getElementById('btn-spinner').style.display = 'block';
    });
</script>

</body>
</html>
