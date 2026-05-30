<?php

namespace App\Filament\Vendeur\Resources\Devises\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeviseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations de la devise')
                    ->icon('heroicon-o-currency-euro')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Code')
                                    ->required()
                                    ->maxLength(3)
                                    ->placeholder('EUR, USD, GBP...')
                                    ->helperText('Code ISO 4217 (3 lettres)')
                                    ->unique(ignoreRecord: true),

                                TextInput::make('symbole')
                                    ->label('Symbole')
                                    ->required()
                                    ->maxLength(5)
                                    ->placeholder('€, $, £...'),

                                TextInput::make('taux_change')
                                    ->label('Taux de change')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->step(0.0001)
                                    ->helperText('Taux par rapport à la devise de référence'),

                                ToggleButtons::make('est_reference')
                                    ->label('Devise de référence')
                                    ->options([
                                        true => 'Oui',
                                        false => 'Non',
                                    ])
                                    ->colors([
                                        true => 'success',
                                        false => 'gray',
                                    ])
                                    ->icons([
                                        true => 'heroicon-o-star',
                                        false => 'heroicon-o-x-mark',
                                    ])
                                    ->inline()
                                    ->default(false)
                                    ->helperText('Une seule devise peut être la référence'),
                            ]),
                    ]),
            ]);
    }
}
