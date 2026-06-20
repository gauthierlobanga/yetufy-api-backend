<?php

namespace App\Filament\Vendeur\Resources\AbandonPaniers\Schemas;

use App\Models\Panier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AbandonPanierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Informations du panier abandonné')
                            ->icon('heroicon-o-shopping-cart')
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                                        Select::make('panier_id')
                                            ->label('Panier')
                                            ->relationship('panier', 'id', fn ($query) => $query->select('id', 'total_general'))
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $panier = Panier::find($state);
                                                    if ($panier) {
                                                        $set('valeur_panier', $panier->total_general);
                                                        $set('date_creation', $panier->created_at);
                                                    }
                                                }
                                            }),

                                        // ✅ Remplacer Placeholder par TextInput disabled
                                        TextInput::make('valeur_panier')
                                            ->label('Valeur du panier')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($get) => number_format($get('valeur_panier') ?? 0, 2).' €'),

                                        Select::make('etape_abandon')
                                            ->label('Étape d\'abandon')
                                            ->options([
                                                'panier' => 'Panier',
                                                'identification' => 'Identification',
                                                'livraison' => 'Livraison',
                                                'paiement' => 'Paiement',
                                                'confirmation' => 'Confirmation',
                                            ])
                                            ->required()
                                            ->default('panier'),

                                        Select::make('raison')
                                            ->label('Raison présumée')
                                            ->options([
                                                'prix_trop_eleve' => 'Prix trop élevé',
                                                'frais_livraison' => 'Frais de livraison trop élevés',
                                                'creation_compte' => 'Création de compte obligatoire',
                                                'probleme_technique' => 'Problème technique',
                                                'comparaison_prix' => 'Comparaison des prix',
                                                'autre' => 'Autre raison',
                                            ])
                                            ->nullable(),
                                    ]),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Statistiques de relance')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                TextInput::make('nombre_relances')
                                    ->label('Nombre de relances')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                DateTimePicker::make('derniere_relance')
                                    ->label('Dernière relance')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),

                                ToggleButtons::make('recupere')
                                    ->label('Récupéré')
                                    ->options([
                                        true => 'Oui',
                                        false => 'Non',
                                    ])
                                    ->colors([
                                        true => 'success',
                                        false => 'danger',
                                    ])
                                    ->icons([
                                        true => 'heroicon-o-check-circle',
                                        false => 'heroicon-o-x-circle',
                                    ])
                                    ->inline()
                                    ->default(false),

                                DateTimePicker::make('date_recuperation')
                                    ->label('Date de récupération')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->visible(fn ($get) => $get('recupere')),

                                // ✅ Remplacer Placeholder par TextInput disabled
                                TextInput::make('score_priorite')
                                    ->label('Score de priorité')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->score_priorite ?? 'N/A')
                                    ->visible(fn ($record) => $record !== null),
                            ]),
                    ]),

                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Données analytiques')
                            ->icon('heroicon-o-chart-bar')
                            ->collapsible()
                            ->schema([
                                KeyValue::make('analytics_data')
                                    ->label('Données analytiques')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter une donnée')
                                    ->reorderable()
                                    ->helperText('Informations supplémentaires sur l\'abandon (source, comportement, etc.)'),
                            ]),
                    ]),
            ]);
    }
}
