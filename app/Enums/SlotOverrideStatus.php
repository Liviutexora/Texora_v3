<?php

namespace App\Enums;

enum SlotOverrideStatus: string
{
    case Blocked = 'blocked';
    case Reserved = 'reserved';

    public function label(): string
    {
        return match ($this) {
            self::Blocked => 'Blocked',
            self::Reserved => 'Reserved',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
