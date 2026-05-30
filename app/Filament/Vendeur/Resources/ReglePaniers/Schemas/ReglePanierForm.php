<?php

namespace App\Filament\Vendeur\Resources\ReglePaniers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReglePanierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Configuration de la règle')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(1)
                            ->schema([

                                Select::make('panier_id')
                                    ->label('Panier')
                                    ->relationship('panier', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('type')
                                    ->label('Type de règle')
                                    ->options([
                                        'remise_pourcentage' => 'Remise en pourcentage',
                                        'remise_montant' => 'Remise en montant',
                                        'livraison_offerte' => 'Livraison offerte',
                                        'produit_offert' => 'Produit offert',
                                        'code_promo' => 'Code promo',
                                    ])
                                    ->required()
                                    ->default('livraison_offerte')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state === 'livraison_offerte') {
                                            $set('valeur', 0);
                                        }
                                    }),

                                TextInput::make('code')
                                    ->label('Code')
                                    ->maxLength(255)
                                    ->placeholder('PROMO2024')
                                    ->visible(fn ($get) => $get('type') === 'code_promo'),

                                TextInput::make('valeur')
                                    ->label('Valeur')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix(fn ($get) => $get('type') === 'remise_pourcentage' ? '%' : '€')
                                    ->visible(fn ($get) => ! in_array($get('type'), ['livraison_offerte', 'produit_offert'])),

                                ToggleButtons::make('appliquee')
                                    ->label('Appliquée')
                                    ->options([
                                        true => 'Oui',
                                        false => 'Non',
                                    ])
                                    ->colors([
                                        true => 'success',
                                        false => 'gray',
                                    ])
                                    ->inline()
                                    ->default(false),

                                DateTimePicker::make('applied_at')
                                    ->label('Date d\'application')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->visible(fn ($get) => $get('appliquee')),
                            ]),
                    ]),

                Section::make('Conditions et résultat')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columnSpan(1)
                    ->schema([
                        KeyValue::make('conditions')
                            ->label('Conditions')
                            ->keyLabel('Condition')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter une condition')
                            ->reorderable()
                            ->helperText('Ex: montant_minimum: 50, produit_ids: [1,2,3]'),

                        KeyValue::make('resultat')
                            ->label('Résultat')
                            ->keyLabel('Propriété')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter un résultat')
                            ->reorderable()
                            ->helperText('Montant de la réduction, description, etc.'),
                    ]),
            ]);
    }
}
