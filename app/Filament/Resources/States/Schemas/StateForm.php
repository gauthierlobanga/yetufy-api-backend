<?php

namespace App\Filament\Resources\States\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class StateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([

                    Select::make('country_id')
                        ->relationship('country', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('name')
                        ->required(),

                    TextInput::make('country_code')
                        ->maxLength(2),

                ]),
            ]);
    }
}
