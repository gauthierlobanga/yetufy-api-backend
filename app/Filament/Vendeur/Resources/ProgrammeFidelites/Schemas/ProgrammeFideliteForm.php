<?php

namespace App\Filament\Vendeur\Resources\ProgrammeFidelites\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgrammeFideliteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations générales')
                    ->icon('heroicon-o-star')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(1)
                            ->schema([

                                TextInput::make('nom')
                                    ->label('Nom du programme')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Programme de fidélité Premium'),

                                ToggleButtons::make('type')
                                    ->label('Type de programme')
                                    ->options([
                                        'points' => 'Points de fidélité',
                                        'paliers' => 'Paliers',
                                        'cashback' => 'Cashback',
                                    ])
                                    ->required()
                                    ->default('points')
                                    ->inline(),
                            ]),
                    ]),

                Section::make('Validité')
                    ->icon('heroicon-o-calendar')
                    ->columnSpan(1)
                    ->schema([
                        DateTimePicker::make('date_debut')
                            ->label('Date de début')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        DateTimePicker::make('date_fin')
                            ->label('Date de fin')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),
                    ]),

                Section::make('Règles du programme')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columnSpanFull()
                    ->schema([
                        KeyValue::make('regles')
                            ->label('Règles de calcul')
                            ->keyLabel('Règle')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter une règle')
                            ->reorderable()
                            ->default([
                                'gain_type' => 'montant',
                                'gain_valeur' => 1,
                                'gain_points' => 1,
                                'taux_conversion' => 100,
                            ])
                            ->helperText('Ex: gain_type: montant, gain_valeur: 1, gain_points: 1, taux_conversion: 100'),

                        KeyValue::make('seuils')
                            ->label('Seuils de niveaux')
                            ->keyLabel('Niveau')
                            ->valueLabel('Points requis')
                            ->addActionLabel('Ajouter un niveau')
                            ->default([
                                'bronze' => 0,
                                'argent' => 500,
                                'or' => 2000,
                                'platine' => 5000,
                                'diamant' => 10000,
                            ])
                            ->helperText('Définissez les paliers de points pour chaque niveau'),
                    ]),

                Section::make('Récompenses')
                    ->icon('heroicon-o-gift')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('recompenses')
                            ->label('Récompenses disponibles')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('nom')
                                            ->label('Nom')
                                            ->required(),

                                        TextInput::make('points_requis')
                                            ->label('Points requis')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1),

                                        TextInput::make('valeur')
                                            ->label('Valeur')
                                            ->numeric()
                                            ->step(0.01)
                                            ->prefix('€'),

                                        Select::make('type')
                                            ->label('Type')
                                            ->options([
                                                'reduction' => 'Réduction',
                                                'produit_offert' => 'Produit offert',
                                                'livraison_offerte' => 'Livraison offerte',
                                            ])
                                            ->default('reduction'),
                                    ]),
                            ])
                            ->columns(1)
                            ->addActionLabel('Ajouter une récompense')
                            ->defaultItems(0)
                            ->collapsible(),
                    ]),
            ]);
    }
}
