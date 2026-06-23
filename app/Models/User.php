<?php

namespace App\Models;

use App\Events\ForgotPasswordRequested;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use WhichBrowser\Parser;
use App\Models\Tenant;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('LoginActivity')
            ->setDescriptionForEvent(fn (string $eventName) => "LoginActivity has been {$eventName}");
    }

    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone_number',
        'profile_photo',
        'is_active',
        'tenant_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_enabled',
        'password_changed_at',
        'password_expires_at',
        'password_history',
        'failed_login_attempts',
        'locked_until',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'password_history', // Hide password history from API responses
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'password_changed_at' => 'datetime',
        'password_expires_at' => 'datetime',
        'password_history' => 'array',
        'locked_until' => 'datetime',
    ];

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'  => $this->hasRole('super_admin'),
            'tenant' => $this->hasAnyRole(['super_admin', 'tenant_owner', 'staff']),
            default  => false,
        };
    }

    // Force single role per user (helper method)
    public function assignRoleSingle(string $role): void
    {
        $this->syncRoles([$role]); // ensures only one role exists
    }

    public function assignSingleRole(string $role): self
    {
        $this->syncRoles([$role]);

        return $this;
    }

    public function getRoleAttribute(): ?string
    {
        return $this->roles->first()?->name;
    }

    /** Profile photo URL (for display). Uses public disk. */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (empty($this->profile_photo)) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->profile_photo);
    }

    /** Backward compatibility: frontend headers use $user->avatar */
    public function getAvatarAttribute(): ?string
    {
        return $this->profile_photo;
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** The Tenant this user owns (via owner_id). Used in the admin Users list. */
    public function ownedTenant(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Tenant::class, 'owner_id');
    }

    public function provider(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Provider::class);
    }

    public function slotReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SlotReservation::class, 'user_id');
    }

    // Relationships
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function ipRestrictions()
    {
        return $this->hasMany(IpRestriction::class);
    }

    // 2FA Methods
    public function enableTwoFactorAuthentication()
    {
        $google2fa = new Google2FA;

        $this->forceFill([
            'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    public function disableTwoFactorAuthentication()
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    public function getTwoFactorQrCodeUrl()
    {
        $google2fa = new Google2FA;

        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            decrypt($this->two_factor_secret)
        );
    }

    public function verifyTwoFactorCode($code)
    {
        try {
            $google2fa = new Google2FA;

            return $google2fa->verifyKey(
                decrypt($this->two_factor_secret),
                $code
            );
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error('2FA verification failed', [
                'user_id' => $this->id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return false;
        }
    }

    public function useRecoveryCode($code)
    {
        try {
            $codes = json_decode(decrypt($this->two_factor_recovery_codes), true);

            if (in_array($code, $codes)) {
                $codes = array_diff($codes, [$code]);

                $this->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
                ])->save();

                return true;
            }

            return false;
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error('2FA recovery code usage failed', [
                'user_id' => $this->id,
                'error' => $th->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Regenerate 2FA recovery codes. Invalidates all existing codes.
     * Returns the new plaintext codes for display once.
     */
    public function regenerateRecoveryCodes(): array
    {
        $codes = $this->generateRecoveryCodes();

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        return $codes;
    }

    protected function generateRecoveryCodes()
    {
        $codes = [];

        for ($i = 0; $i < 8; $i++) {
            $codes[] = sprintf('%04d-%04d', random_int(0, 9999), random_int(0, 9999));
        }

        return $codes;
    }

    // Password Policy Methods
    public function isPasswordExpired()
    {
        if (! $this->password_expires_at) {
            return false;
        }

        return Carbon::now()->isAfter($this->password_expires_at);
    }

    public function updatePassword($password)
    {
        $history = $this->password_history ?? [];
        $history[] = [
            'password' => $this->password,
            'changed_at' => $this->password_changed_at ?? now(),
        ];

        // Keep only last N passwords based on policy
        $policy = PasswordPolicy::getDefault();
        if ($policy && count($history) > $policy->history_count) {
            $history = array_slice($history, -$policy->history_count);
        }

        $expiresAt = null;
        if ($policy && $policy->expires_days) {
            $expiresAt = now()->addDays($policy->expires_days);
        }

        $this->forceFill([
            'password' => Hash::make($password),
            'password_changed_at' => now(),
            'password_expires_at' => $expiresAt,
            'password_history' => $history,
        ])->save();
    }

    public function isPasswordInHistory($password)
    {
        $history = $this->password_history ?? [];

        foreach ($history as $item) {
            if (Hash::check($password, $item['password'])) {
                return true;
            }
        }

        return false;
    }

    // Account Security Methods
    public function incrementFailedLoginAttempts()
    {
        $this->increment('failed_login_attempts');

        $policy = PasswordPolicy::getDefault();
        if ($policy && $this->failed_login_attempts >= $policy->max_login_attempts) {
            $this->forceFill([
                'locked_until' => now()->addMinutes($policy->lockout_duration),
            ])->save();
        }
    }

    public function resetFailedLoginAttempts()
    {
        $this->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }

    public function isLocked()
    {
        return $this->locked_until && Carbon::now()->isBefore($this->locked_until);
    }

    // Session Management
    public function activeSessions()
    {
        return $this->sessions()->where('is_active', true);
    }

    public function logSession($sessionId, $request)
    {
        $parser = new Parser($request->userAgent());

        return $this->sessions()->create([
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $parser->device->type ?? 'Unknown',
            'device_name' => $parser->device->toString() ?? 'Unknown',
            'browser' => $parser->browser->toString() ?? 'Unknown',
            'platform' => $parser->os->toString() ?? 'Unknown',
            'location' => $this->getLocationFromIp($request->ip()),
            'last_activity' => now(),
        ]);
    }

    protected function getLocationFromIp($ip)
    {
        // Implement IP geolocation here
        // You can use services like ipapi.co or geoip2
        return 'Unknown';
    }

    /**
     * Send the password reset notification.
     * Override to use custom email template via event/listener/job.
     */
    public function sendPasswordResetNotification($token): void
    {
        $resetLink = url(route('password.reset', [
            'token' => $token,
        ], false).'?email='.urlencode($this->email));
        // Fire event to send email using custom template
        event(new ForgotPasswordRequested($this, $resetLink));
    }
}
