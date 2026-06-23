<?php

namespace App\Support;

use App\Models\Setting;

final class GuestAppointmentVerificationMode
{
    public const WITHOUT_OTP = 'without_otp';

    public const EMAIL_OTP = 'email_otp';

    public const PHONE_OTP = 'phone_otp';

    public static function current(): string
    {
        $value = Setting::get('guest_appointment_verification_mode', self::WITHOUT_OTP);

        return match ($value) {
            self::EMAIL_OTP => self::EMAIL_OTP,
            self::PHONE_OTP => self::PHONE_OTP,
            default => self::WITHOUT_OTP,
        };
    }

    public static function isWithoutOtp(): bool
    {
        return self::current() === self::WITHOUT_OTP;
    }

    public static function isEmailOtp(): bool
    {
        return self::current() === self::EMAIL_OTP;
    }

    public static function isPhoneOtp(): bool
    {
        return self::current() === self::PHONE_OTP;
    }
}
