<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Setting extends Model
{
    use LogsActivity;

    /** Defaults for the header top bar (used when keys are missing in `settings`). */
    public const HEADER_BAR_DEFAULTS = [
        'contact_email' => 'info@example.com',
        'contact_phone' => '+1 555-9990-153',
        'contact_address' => '88 Brooklyn Street, New York',
        'contact_office_hours' => 'Mon - Sat: 8:00AM - 7:00PM',
    ];

    private static bool $headerBarDefaultsEnsured = false;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('LoginActivity')
            ->setDescriptionForEvent(fn(string $eventName) => "LoginActivity has been {$eventName}");
    }

    protected $fillable = [
        'group', 'key', 'value', 'type', 'options', 'description', 'is_public'
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
    ];

    public static function get($key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            try {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            } catch (\Throwable $e) {
                return $default;
            }
        });
    }

    public static function set($key, $value, $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
        
        Cache::forget("setting.{$key}");
        
        return $setting;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::saved(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
        
        static::deleted(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
    }

    /**
     * Create missing header bar contact settings with defaults (one-time per request).
     *
     * @return array{email: string, phone: string, address: string, hours: string}
     */
    public static function headerBarContact(): array
    {
        self::ensureClinicHeaderBarDefaults();

        return [
            'email' => self::headerBarValue('contact_email'),
            'phone' => self::headerBarValue('contact_phone'),
            'address' => self::headerBarValue('contact_address'),
            'hours' => self::headerBarValue('contact_office_hours'),
        ];
    }

    public static function ensureClinicHeaderBarDefaults(): void
    {
        if (self::$headerBarDefaultsEnsured) {
            return;
        }
        self::$headerBarDefaultsEnsured = true;

        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        foreach (self::HEADER_BAR_DEFAULTS as $key => $value) {
            self::query()->firstOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'general',
                    'type' => 'text',
                ]
            );
        }
    }

    private static function headerBarValue(string $key): string
    {
        $default = self::HEADER_BAR_DEFAULTS[$key] ?? '';
        $raw = self::get($key, $default);
        if ($raw === null || $raw === '') {
            return $default;
        }

        return (string) $raw;
    }
}