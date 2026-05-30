<?php

namespace App\Filament\Resources\Timezones\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TimezoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
