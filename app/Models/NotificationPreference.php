<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class NotificationPreference extends Model
{
    use HasFactory;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('NotificationPreference')
            ->setDescriptionForEvent(fn(string $eventName) => "NotificationPreference has been {$eventName}");
    }

    protected $fillable = [
        'user_id',
        'permission_name',
        'email',
        'sms',
        'web_notification',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check whether email sending is enabled for a given permission/event.
     *
     * Opt-out model: emails are ENABLED by default (when no preference record exists).
     * An admin must explicitly save email=false to disable a type.
     * If any admin has email=true, it overrides any disabled records.
     */
    public static function isEmailEnabled(string $permissionName): bool
    {
        $prefs = static::where('permission_name', $permissionName)->get();
        return $prefs->isEmpty() || $prefs->contains('email', true);
    }

    /**
     * SMS is opt-in: at least one super-admin must enable the permission.
     */
    public static function isSmsEnabled(string $permissionName): bool
    {
        return static::query()
            ->where('permission_name', $permissionName)
            ->where('sms', true)
            ->exists();
    }
}
