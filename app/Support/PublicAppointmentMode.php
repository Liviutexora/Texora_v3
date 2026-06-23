<?php

namespace App\Support;

use App\Models\Setting;

final class PublicAppointmentMode
{
    public const GENERALIZED = 'generalized';

    public const PROVIDER_SPECIFIC = 'provider_specific';

    public static function current(): string
    {
        $v = Setting::get('appointment_booking_mode', self::GENERALIZED);

        return $v === self::PROVIDER_SPECIFIC ? self::PROVIDER_SPECIFIC : self::GENERALIZED;
    }

    public static function isGeneralized(): bool
    {
        return self::current() === self::GENERALIZED;
    }

    public static function isProviderSpecific(): bool
    {
        return self::current() === self::PROVIDER_SPECIFIC;
    }
}
