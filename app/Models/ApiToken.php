<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ApiToken extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'token',
        'token_prefix',
        'status',
    ];

    // Holds the plaintext token after creation — not persisted, shown once to user
    public ?string $plainTextToken = null;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'name', 'status', 'token_prefix'])
            ->useLogName('ApiToken')
            ->setDescriptionForEvent(fn(string $eventName) => "ApiToken has been {$eventName}");
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->token)) {
                $plain = Str::random(40);
                $model->plainTextToken = $plain;
                $model->token_prefix   = substr($plain, 0, 8);
                $model->token          = hash('sha256', $plain);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
