<?php

namespace App\Filament\Vendeur\Resources\LivraisonPaniers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LivraisonPanierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations de livraison')
                    ->icon('heroicon-o-truck')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                Select::make('panier_id')
                                    ->label('Panier')
                                    ->relationship('panier', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('adresse_id')
                                    ->label('Adresse de livraison')
                                    ->relationship('adresse', 'adresse_complete')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('mode')
                                    ->label('Mode de livraison')
                                    ->options([
                                        'domicile' => 'Livraison à domicile',
                                        'point_relais' => 'Point relais',
                                        'express' => 'Livraison express',
                                        'retrait_magasin' => 'Retrait en magasin',
                                    ])
                                    ->preload()
                                    ->searchable()
                                    ->required(),

                                TextInput::make('cout')
                                    ->label('Coût de livraison')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€'),

                                DateTimePicker::make('date_estimee')
                                    ->label('Date estimée')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),

                                DateTimePicker::make('selected_at')
                                    ->label('Sélectionné le')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),

                                KeyValue::make('options')
                                    ->label('Options supplémentaires')
                                    ->keyLabel('Option')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter une option')
                                    ->reorderable()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
