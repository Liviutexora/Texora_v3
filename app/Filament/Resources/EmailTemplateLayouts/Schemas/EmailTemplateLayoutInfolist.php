<?php

namespace App\Filament\Resources\EmailTemplateLayouts\Schemas;

use Filament\Schemas\Schema;

class EmailTemplateLayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->headerActions([])
            ->components([
                //
            ]);
    }
}
