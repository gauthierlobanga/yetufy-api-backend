<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([

                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('iso2')
                        ->required()
                        ->maxLength(2),

                    TextInput::make('iso3')
                        ->required()
                        ->maxLength(3),

                    TextInput::make('phone_code')
                        ->tel()
                        ->required(),

                    TextInput::make('region')
                        ->required(),

                    TextInput::make('subregion')
                        ->required(),

                    Toggle::make('status')
                        ->label('Actif')
                        ->default(true),

                ]),
            ]);
    }
}
