<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserVisit extends Model
{
    use HasFactory;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('LoginActivity')
            ->setDescriptionForEvent(fn(string $eventName) => "LoginActivity has been {$eventName}");
    }

    protected $fillable = [
        'ip_address',
        'user_id',
        'user_agent',
        'session_id',
    ];
}
