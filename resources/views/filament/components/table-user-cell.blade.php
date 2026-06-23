@php
    $isProvider = $record instanceof \App\Models\Provider;
    $user = $isProvider && $record->user
        ? (object) [
            'name' => $record->user->name ?? '—',
            'email' => $record->user->email ?? null,
            'profile_photo_url' => $record->user->profile_photo_url ?? null,
        ]
        : (object) [
            'name' => $record->name ?? '—',
            'email' => $record->email ?? null,
            'profile_photo_url' => $record->profile_photo_url ?? null,
        ];
    $initials = collect(explode(' ', trim($user->name)))->filter()->map(fn ($p) => strtoupper(mb_substr($p, 0, 1)))->take(2)->join('') ?: '?';
    $hasPhoto = !empty($user->profile_photo_url);
@endphp
<div class="flex items-center gap-3 w-full min-w-0">
    {{-- Avatar --}}
    <div class="w-10 h-10 min-w-[40px] shrink-0 rounded-full overflow-hidden border-2 border-black/[0.08] bg-gray-200 flex items-center justify-center text-sm font-semibold text-gray-600 dark:border-white/20 dark:bg-white/10 dark:text-gray-300">
        @if($hasPhoto)
            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover object-center block" />
        @else
            <span>{{ $initials }}</span>
        @endif
    </div>
    {{-- Name & email --}}
    <div class="flex-1 min-w-0 flex flex-col justify-center gap-0.5">
        <div class="text-sm font-semibold leading-tight truncate text-gray-900 dark:text-white">{{ $user->name }}</div>
        @if($user->email)
            <div class="text-xs leading-tight truncate text-gray-500 dark:text-gray-300">{{ $user->email }}</div>
        @endif
    </div>
</div>
