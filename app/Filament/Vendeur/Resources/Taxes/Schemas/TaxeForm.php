<?php

namespace App\Filament\Vendeur\Resources\Taxes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations de la taxe')
                    ->icon('heroicon-o-calculator')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextInput::make('nom')
                                    ->label('Nom de la taxe')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('TVA 20%'),

                                TextInput::make('code')
                                    ->label('Code')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('TVA20')
                                    ->helperText('Code unique identifiant la taxe'),

                                TextInput::make('taux')
                                    ->label('Taux (%)')
                                    ->required()
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%'),

                                Select::make('pays')
                                    ->label('Pays')
                                    ->options([
                                        'France' => 'France',
                                        'Belgique' => 'Belgique',
                                        'Suisse' => 'Suisse',
                                        'Canada' => 'Canada',
                                        'États-Unis' => 'États-Unis',
                                        'Royaume-Uni' => 'Royaume-Uni',
                                        'Allemagne' => 'Allemagne',
                                        'Espagne' => 'Espagne',
                                        'Italie' => 'Italie',
                                        'Autre' => 'Autre',
                                    ])
                                    ->searchable(),

                                TextInput::make('region')
                                    ->label('Région / État')
                                    ->maxLength(255)
                                    ->placeholder('Optionnel'),

                                ToggleButtons::make('est_defaut')
                                    ->label('Taxe par défaut')
                                    ->options([
                                        true => 'Oui',
                                        false => 'Non',
                                    ])
                                    ->colors([
                                        true => 'success',
                                        false => 'gray',
                                    ])
                                    ->icons([
                                        true => 'heroicon-o-check-circle',
                                        false => 'heroicon-o-x-circle',
                                    ])
                                    ->inline()
                                    ->default(false)
                                    ->helperText('Appliquée automatiquement si aucune autre taxe ne correspond'),
                            ]),
                    ]),
            ]);
    }
}
