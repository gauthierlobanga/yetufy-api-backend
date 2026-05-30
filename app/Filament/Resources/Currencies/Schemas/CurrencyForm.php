<?php

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('country_id')
                    ->label('Pays')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label('Nom de la devise')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Ex: Euro'),

                TextInput::make('code')
                    ->label('Code ISO')
                    ->required()
                    ->maxLength(3)
                    ->hint('Ex: EUR, USD')
                    ->afterStateUpdated(fn ($set, $state) => $set('code', strtoupper($state))),

                TextInput::make('symbol')
                    ->label('Symbole')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('€, $'),

                TextInput::make('symbol_native')
                    ->label('Symbole natif')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('€, $'),

                TextInput::make('precision')
                    ->label('Décimales')
                    ->numeric()
                    ->required()
                    ->default(2)
                    ->minValue(0)
                    ->maxValue(4)
                    ->hint('Nombre de chiffres après la virgule'),

                TextInput::make('decimal_mark')
                    ->label('Séparateur décimal')
                    ->required()
                    ->default('.')
                    ->maxLength(1)
                    ->hint('. ou ,'),

                TextInput::make('thousands_separator')
                    ->label('Séparateur de milliers')
                    ->required()
                    ->default(',')
                    ->maxLength(1)
                    ->hint(', ou espace'),

                TextInput::make('symbol_first')
                    ->label('Position du symbole')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(0)
                    ->maxValue(1)
                    ->hint('1 = avant le montant, 0 = après'),
            ]);
    }
}
