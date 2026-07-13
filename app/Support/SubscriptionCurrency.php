<?php

namespace App\Support;

class SubscriptionCurrency
{
    /**
     * Resolve the subscription display symbol from the same settings-backed
     * currency source used for default currency selection.
     */
    public static function symbol(): string
    {
        $code = self::defaultCode();

        return self::symbolForCode($code) ?: 'RON';
    }

    public static function defaultCode(): string
    {
        $currencies = LocalisationOptions::currencies();
        $code = is_array($currencies) ? array_key_first($currencies) : null;
        $code = strtoupper((string) ($code ?? ''));

        return $code !== '' ? $code : 'RON';
    }

    public static function symbolForCode(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return '';
        }

        return match ($code) {
            'USD' => '$',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            'INR' => 'INR',
            'RON' => 'RON',
            'AUD' => 'AUD',
            'CAD' => 'CAD',
            'SGD' => 'SGD',
            'AED' => 'AED',
            'CHF' => 'CHF',
            'JPY' => 'JPY',
            'NZD' => 'NZD',
            'HKD' => 'HKD',
            'SEK' => 'SEK',
            'NOK' => 'NOK',
            'DKK' => 'DKK',
            'MYR' => 'MYR',
            'BRL' => 'BRL',
            'ZAR' => 'ZAR',
            'MXN' => 'MXN',
            'PHP' => 'PHP',
            default => '',
        };
    }
}