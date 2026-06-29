<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFollowupHistory extends Model
{
    protected $table = 'customer_followup_history';

    public $timestamps = false;

    protected $fillable = [
        'followup_id',
        'action',
        'channel',
        'notes',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function followup(): BelongsTo
    {
        return $this->belongsTo(CustomerFollowup::class, 'followup_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
