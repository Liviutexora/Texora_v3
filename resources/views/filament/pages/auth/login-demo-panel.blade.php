{{--
    Demo login quick-pick panel rendered below the Filament login form
    via PanelsRenderHook::AUTH_LOGIN_FORM_AFTER when DEMO_MODE=true.

    - /admin/login → shows Super Admin only (only super_admin can access /admin)
    - /login       → shows Business Owner, Staff, Client (seeded by BookingSaasSeeder)

    All emails must match BookingSaasSeeder / DatabaseSeeder accounts exactly.
--}}
@php
    $isAdminPanel = str_contains(request()->path(), 'admin');

    $accounts = $isAdminPanel
        ? [
            [
                'label'    => 'Super Admin',
                'email'    => 'admin@slotara.app',
                'iconBg'   => '#fee2e2',
                'iconColor'=> '#dc2626',
                'path'     => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            ],
          ]
        : [
            [
                'label'    => 'Business Owner',
                'email'    => 'owner@velvet-chair.demo',
                'iconBg'   => '#dbeafe',
                'iconColor'=> '#2563eb',
                'path'     => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            ],
            [
                'label'    => 'Staff',
                'email'    => 'staff@slotara.app',
                'iconBg'   => '#fef3c7',
                'iconColor'=> '#d97706',
                'path'     => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            ],
            [
                'label'    => 'Client',
                'email'    => 'client@slotara.app',
                'iconBg'   => '#d1fae5',
                'iconColor'=> '#059669',
                'path'     => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            ],
          ];
@endphp

<div class="mt-5">
    <div class="border border-violet-200 rounded-xl overflow-hidden shadow-sm">

        {{-- Header bar --}}
        <div style="background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 100%);"
             class="px-4 py-2.5 flex items-center gap-2">
            <svg class="w-[15px] h-[15px] text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-white text-[11.5px] font-semibold tracking-[0.3px]">
                Demo — click any account to log in instantly
            </span>
        </div>

        {{-- Account rows --}}
        @foreach($accounts as $i => $acc)
        <button
            type="button"
            onclick="slotaraDemoFill('{{ $acc['email'] }}')"
            @class([
                'w-full flex items-center justify-between px-4 py-2.5 bg-white text-left cursor-pointer border-none transition-colors duration-150 hover:bg-violet-50',
                'border-b border-gray-100' => $i < count($accounts) - 1,
            ])
        >
            <div class="flex items-center gap-2.5">
                <span style="background:{{ $acc['iconBg'] }};color:{{ $acc['iconColor'] }};"
                      class="w-8 h-8 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $acc['path'] }}"/>
                    </svg>
                </span>
                <div>
                    <p class="text-[13px] font-semibold text-gray-800 m-0 leading-[1.3]">{{ $acc['label'] }}</p>
                    <p class="text-[11px] text-gray-400 m-0 leading-[1.3]">{{ $acc['email'] }}</p>
                </div>
            </div>
            <span class="text-[11px] font-mono bg-gray-100 text-gray-500 px-2 py-[3px] rounded">{{ __('password') }}</span>
        </button>
        @endforeach

    </div>
</div>

@vite('resources/js/login-demo-panel.js')
