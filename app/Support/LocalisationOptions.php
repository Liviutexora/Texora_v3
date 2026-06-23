<?php

namespace App\Support;

use App\Models\Setting;

class LocalisationOptions
{
    /**
     * Timezone list for dropdowns.
     * Uses admin-configured allowed list, or falls back to all PHP identifiers.
     */
    public static function timezones(): array
    {
        $stored = Setting::get('allowed_timezones');

        if ($stored && trim($stored)) {
            $list = array_values(array_filter(array_map('trim', explode(',', $stored))));
            if (count($list) > 0) {
                return $list;
            }
        }

        return \DateTimeZone::listIdentifiers();
    }

    /**
     * Timezone options formatted for Filament Select: ['TZ/Name' => 'TZ/Name'].
     */
    public static function timezoneSelectOptions(): array
    {
        return collect(static::timezones())
            ->mapWithKeys(fn ($tz) => [$tz => $tz])
            ->all();
    }

    /**
     * Currency options: ['USD' => 'USD — US Dollar', ...].
     * Uses admin-configured allowed list, or falls back to a comprehensive default set.
     */
    public static function currencies(): array
    {
        $stored = Setting::get('allowed_currencies');

        if ($stored && trim($stored)) {
            $codes = array_values(array_filter(array_map('trim', explode(',', $stored))));
            if (count($codes) > 0) {
                $all = static::allCurrencies();
                return collect($codes)
                    ->mapWithKeys(fn ($code) => [$code => $all[$code] ?? $code])
                    ->all();
            }
        }

        return static::allCurrencies();
    }

    /** Default subset shown when admin hasn't configured a custom list. */
    public static function defaultCurrencies(): array
    {
        return [
            'USD' => 'USD — US Dollar',
            'EUR' => 'EUR — Euro',
            'GBP' => 'GBP — British Pound',
            'INR' => 'INR — Indian Rupee',
            'AUD' => 'AUD — Australian Dollar',
            'CAD' => 'CAD — Canadian Dollar',
            'SGD' => 'SGD — Singapore Dollar',
            'AED' => 'AED — UAE Dirham',
            'CHF' => 'CHF — Swiss Franc',
            'JPY' => 'JPY — Japanese Yen',
            'NZD' => 'NZD — New Zealand Dollar',
            'HKD' => 'HKD — Hong Kong Dollar',
            'SEK' => 'SEK — Swedish Krona',
            'NOK' => 'NOK — Norwegian Krone',
            'DKK' => 'DKK — Danish Krone',
            'MYR' => 'MYR — Malaysian Ringgit',
            'BRL' => 'BRL — Brazilian Real',
            'ZAR' => 'ZAR — South African Rand',
            'MXN' => 'MXN — Mexican Peso',
            'PHP' => 'PHP — Philippine Peso',
        ];
    }

    /** Full currency map used when admin picks from currency codes. */
    public static function allCurrencies(): array
    {
        return [
            'AED' => 'AED — UAE Dirham',          'AFN' => 'AFN — Afghan Afghani',
            'ALL' => 'ALL — Albanian Lek',          'AMD' => 'AMD — Armenian Dram',
            'ANG' => 'ANG — Netherlands Antillean Guilder', 'AOA' => 'AOA — Angolan Kwanza',
            'ARS' => 'ARS — Argentine Peso',        'AUD' => 'AUD — Australian Dollar',
            'AWG' => 'AWG — Aruban Florin',         'AZN' => 'AZN — Azerbaijani Manat',
            'BAM' => 'BAM — Bosnia-Herzegovina Convertible Mark',
            'BBD' => 'BBD — Barbadian Dollar',      'BDT' => 'BDT — Bangladeshi Taka',
            'BGN' => 'BGN — Bulgarian Lev',         'BHD' => 'BHD — Bahraini Dinar',
            'BIF' => 'BIF — Burundian Franc',       'BMD' => 'BMD — Bermudan Dollar',
            'BND' => 'BND — Brunei Dollar',         'BOB' => 'BOB — Bolivian Boliviano',
            'BRL' => 'BRL — Brazilian Real',        'BSD' => 'BSD — Bahamian Dollar',
            'BTN' => 'BTN — Bhutanese Ngultrum',    'BWP' => 'BWP — Botswanan Pula',
            'BYN' => 'BYN — Belarusian Ruble',      'BZD' => 'BZD — Belize Dollar',
            'CAD' => 'CAD — Canadian Dollar',       'CDF' => 'CDF — Congolese Franc',
            'CHF' => 'CHF — Swiss Franc',           'CLP' => 'CLP — Chilean Peso',
            'CNY' => 'CNY — Chinese Yuan',          'COP' => 'COP — Colombian Peso',
            'CRC' => 'CRC — Costa Rican Colón',     'CUP' => 'CUP — Cuban Peso',
            'CVE' => 'CVE — Cape Verdean Escudo',   'CZK' => 'CZK — Czech Koruna',
            'DJF' => 'DJF — Djiboutian Franc',      'DKK' => 'DKK — Danish Krone',
            'DOP' => 'DOP — Dominican Peso',        'DZD' => 'DZD — Algerian Dinar',
            'EGP' => 'EGP — Egyptian Pound',        'ERN' => 'ERN — Eritrean Nakfa',
            'ETB' => 'ETB — Ethiopian Birr',        'EUR' => 'EUR — Euro',
            'FJD' => 'FJD — Fijian Dollar',         'GBP' => 'GBP — British Pound',
            'GEL' => 'GEL — Georgian Lari',         'GHS' => 'GHS — Ghanaian Cedi',
            'GMD' => 'GMD — Gambian Dalasi',        'GTQ' => 'GTQ — Guatemalan Quetzal',
            'HKD' => 'HKD — Hong Kong Dollar',      'HNL' => 'HNL — Honduran Lempira',
            'HRK' => 'HRK — Croatian Kuna',         'HTG' => 'HTG — Haitian Gourde',
            'HUF' => 'HUF — Hungarian Forint',      'IDR' => 'IDR — Indonesian Rupiah',
            'ILS' => 'ILS — Israeli New Shekel',    'INR' => 'INR — Indian Rupee',
            'IQD' => 'IQD — Iraqi Dinar',           'IRR' => 'IRR — Iranian Rial',
            'ISK' => 'ISK — Icelandic Króna',       'JMD' => 'JMD — Jamaican Dollar',
            'JOD' => 'JOD — Jordanian Dinar',       'JPY' => 'JPY — Japanese Yen',
            'KES' => 'KES — Kenyan Shilling',       'KGS' => 'KGS — Kyrgystani Som',
            'KHR' => 'KHR — Cambodian Riel',        'KWD' => 'KWD — Kuwaiti Dinar',
            'KYD' => 'KYD — Cayman Islands Dollar', 'KZT' => 'KZT — Kazakhstani Tenge',
            'LAK' => 'LAK — Laotian Kip',           'LBP' => 'LBP — Lebanese Pound',
            'LKR' => 'LKR — Sri Lankan Rupee',      'LYD' => 'LYD — Libyan Dinar',
            'MAD' => 'MAD — Moroccan Dirham',       'MDL' => 'MDL — Moldovan Leu',
            'MKD' => 'MKD — Macedonian Denar',      'MMK' => 'MMK — Myanmar Kyat',
            'MNT' => 'MNT — Mongolian Tögrög',      'MOP' => 'MOP — Macanese Pataca',
            'MUR' => 'MUR — Mauritian Rupee',       'MVR' => 'MVR — Maldivian Rufiyaa',
            'MWK' => 'MWK — Malawian Kwacha',       'MXN' => 'MXN — Mexican Peso',
            'MYR' => 'MYR — Malaysian Ringgit',     'MZN' => 'MZN — Mozambican Metical',
            'NAD' => 'NAD — Namibian Dollar',       'NGN' => 'NGN — Nigerian Naira',
            'NIO' => 'NIO — Nicaraguan Córdoba',    'NOK' => 'NOK — Norwegian Krone',
            'NPR' => 'NPR — Nepalese Rupee',        'NZD' => 'NZD — New Zealand Dollar',
            'OMR' => 'OMR — Omani Rial',            'PAB' => 'PAB — Panamanian Balboa',
            'PEN' => 'PEN — Peruvian Sol',          'PGK' => 'PGK — Papua New Guinean Kina',
            'PHP' => 'PHP — Philippine Peso',       'PKR' => 'PKR — Pakistani Rupee',
            'PLN' => 'PLN — Polish Złoty',          'PYG' => 'PYG — Paraguayan Guaraní',
            'QAR' => 'QAR — Qatari Rial',           'RON' => 'RON — Romanian Leu',
            'RSD' => 'RSD — Serbian Dinar',         'RUB' => 'RUB — Russian Ruble',
            'RWF' => 'RWF — Rwandan Franc',         'SAR' => 'SAR — Saudi Riyal',
            'SEK' => 'SEK — Swedish Krona',         'SGD' => 'SGD — Singapore Dollar',
            'SOS' => 'SOS — Somali Shilling',       'SRD' => 'SRD — Surinamese Dollar',
            'THB' => 'THB — Thai Baht',             'TND' => 'TND — Tunisian Dinar',
            'TRY' => 'TRY — Turkish Lira',          'TTD' => 'TTD — Trinidad & Tobago Dollar',
            'TWD' => 'TWD — New Taiwan Dollar',     'TZS' => 'TZS — Tanzanian Shilling',
            'UAH' => 'UAH — Ukrainian Hryvnia',     'UGX' => 'UGX — Ugandan Shilling',
            'USD' => 'USD — US Dollar',             'UYU' => 'UYU — Uruguayan Peso',
            'UZS' => 'UZS — Uzbekistani Som',       'VES' => 'VES — Venezuelan Bolívar',
            'VND' => 'VND — Vietnamese Đồng',       'XAF' => 'XAF — Central African CFA Franc',
            'XOF' => 'XOF — West African CFA Franc','YER' => 'YER — Yemeni Rial',
            'ZAR' => 'ZAR — South African Rand',    'ZMW' => 'ZMW — Zambian Kwacha',
        ];
    }
}
