<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('user.{id}', function ($user, $id) {
    Log::info('Broadcast auth attempt', [
        'auth_user_id' => $user->id ?? null,
        'channel_id'   => $id,
        'result'       => (int) $user->id === (int) $id,
    ]);

    return (int) $user->id === (int) $id;
});