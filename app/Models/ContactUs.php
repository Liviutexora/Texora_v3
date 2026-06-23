<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ContactUs extends Model
{
    use HasFactory;
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in-progress';
    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_LIST = [
        self::STATUS_NEW         => 'New',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_RESOLVED    => 'Resolved',
    ];

    public const TYPE_LIST = [
        'general'     => 'General Inquiry',
        'support'     => 'Support',
        'sales'       => 'Sales',
        'billing'     => 'Billing',
        'partnership' => 'Partnership',
        'other'       => 'Other',
    ];


    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('ContactUs')
            ->setDescriptionForEvent(fn(string $eventName) => "ContactUs has been {$eventName}");
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'type',
        'message',
        'status',
        'custom_fields',
        'admin_reply',
        'replied_at',
        'replied_by',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'replied_at'    => 'datetime',
    ];

    public function repliedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'replied_by');
    }

    /**
     * Get custom fields as formatted array
     * 
     * @return array
     */
    public function getCustomFieldsFormatted(): array
    {
        if (empty($this->custom_fields)) {
            return [];
        }

        // Ensure format is [{name: "", value: ""}]
        $formatted = [];
        foreach ($this->custom_fields as $field) {
            if (is_array($field) && isset($field['name'])) {
                $formatted[] = [
                    'name' => $field['name'],
                    'value' => $field['value'] ?? '',
                ];
            }
        }

        return $formatted;
    }
}
