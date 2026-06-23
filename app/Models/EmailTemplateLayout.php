<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EmailTemplateLayout extends Model
{
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('EmailTemplateLayout')
            ->setDescriptionForEvent(fn(string $eventName) => "EmailTemplateLayout has been {$eventName}");
    }

    protected $fillable = ['name', 'body', 'is_active'];
}