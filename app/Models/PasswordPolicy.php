<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class PasswordPolicy extends Model
{
    use HasFactory;
    use LogsActivity;
    use HasRoles;

    protected $guard_name = 'web';
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('LoginActivity')
            ->setDescriptionForEvent(fn(string $eventName) => "LoginActivity has been {$eventName}");
    }


    protected $fillable = [
        'name',
        'min_length',
        'require_uppercase',
        'require_lowercase',
        'require_numbers',
        'require_special_chars',
        'expires_days',
        'history_count',
        'max_login_attempts',
        'lockout_duration',
        'is_default',
    ];

    protected $casts = [
        'require_uppercase' => 'boolean',
        'require_lowercase' => 'boolean',
        'require_numbers' => 'boolean',
        'require_special_chars' => 'boolean',
        'is_default' => 'boolean',
    ];

    public static function getDefault()
    {
        return static::first();
    }

    public function setAsDefault()
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    public function getValidationRules()
    {
        $rules = ['required', "min:{$this->min_length}"];
        
        $patterns = [];
        if ($this->require_uppercase) $patterns[] = '(?=.*[A-Z])';
        if ($this->require_lowercase) $patterns[] = '(?=.*[a-z])';
        if ($this->require_numbers) $patterns[] = '(?=.*\d)';
        if ($this->require_special_chars) $patterns[] = '(?=.*[@$!%*?&#])';
        
        if (!empty($patterns)) {
            $rules[] = 'regex:/^' . implode('', $patterns) . '.*/';
        }
        
        return implode('|', $rules);
    }

    public function getDescription()
    {
        $requirements = [];
        
        $requirements[] = "Minimum {$this->min_length} characters";
        if ($this->require_uppercase) $requirements[] = "At least one uppercase letter";
        if ($this->require_lowercase) $requirements[] = "At least one lowercase letter";
        if ($this->require_numbers) $requirements[] = "At least one number";
        if ($this->require_special_chars) $requirements[] = "At least one special character (@$!%*?&#)";
        
        return implode(', ', $requirements);
    }
}
