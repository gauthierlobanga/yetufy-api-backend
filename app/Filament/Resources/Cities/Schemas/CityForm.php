<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Nnjeim\World\Models\State;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('state_id', null))
                    ->required(),

                Select::make('state_id')
                    ->label('Province / État')
                    ->options(fn (Get $get) => State::where('country_id', $get('country_id'))
                        ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),

                TextInput::make('name')
                    ->required(),

                TextInput::make('country_code')
                    ->maxLength(2)
                    ->uppercase(),

            ]);
    }
}
