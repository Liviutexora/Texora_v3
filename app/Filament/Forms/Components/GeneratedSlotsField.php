<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\ViewField;

/**
 * ViewField for generated slot badges. Stays reactive so badges update when shift fields change.
 */
class GeneratedSlotsField extends ViewField
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->live();
    }
}
