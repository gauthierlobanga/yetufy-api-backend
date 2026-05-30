<?php

namespace App\Filament\Vendeur\Resources\Retours\Schemas;

use App\Models\Commande;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
// use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RetourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Informations du retour')
                            ->icon('heroicon-o-arrow-uturn-left')
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                                        Select::make('commande_id')
                                            ->label('Commande')
                                            ->relationship('commande', 'numero_commande')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $commande = Commande::find($state);
                                                    if ($commande) {
                                                        $set('client_nom', $commande->client?->nom);
                                                        $set('montant_total', $commande->total);
                                                    }
                                                }
                                            }),

                                        TextInput::make('reference')
                                            ->label('Référence')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->default(fn () => 'RET-'.strtoupper(uniqid()))
                                            ->disabled()
                                            ->dehydrated(),

                                        Select::make('motif')
                                            ->label('Motif')
                                            ->options([
                                                'defectueux' => 'Produit défectueux',
                                                'mauvaise_taille' => 'Mauvaise taille',
                                                'ne_correspond_pas' => 'Ne correspond pas à la description',
                                                'erreur_commande' => 'Erreur dans la commande',
                                                'produit_abime' => 'Produit abîmé',
                                                'livraison_tardive' => 'Livraison tardive',
                                                'autre' => 'Autre motif',
                                            ])
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state !== 'autre') {
                                                    $set('motif_autre', null);
                                                }
                                            }),

                                        TextInput::make('motif_autre')
                                            ->label('Précisez le motif')
                                            ->maxLength(255)
                                            ->visible(fn ($get) => $get('motif') === 'autre')
                                            ->required(fn ($get) => $get('motif') === 'autre'),

                                        Select::make('action')
                                            ->label('Action')
                                            ->options([
                                                'remboursement' => 'Remboursement',
                                                'avoir' => 'Avoir',
                                                'echange' => 'Échange',
                                            ])
                                            ->required()
                                            ->default('remboursement'),

                                        ToggleButtons::make('statut')
                                            ->label('Statut')
                                            ->options([
                                                'en_attente' => 'En attente',
                                                'accepte' => 'Accepté',
                                                'refuse' => 'Refusé',
                                                'en_cours' => 'En cours',
                                                'termine' => 'Terminé',
                                            ])
                                            ->colors([
                                                'en_attente' => 'warning',
                                                'accepte' => 'success',
                                                'refuse' => 'danger',
                                                'en_cours' => 'info',
                                                'termine' => 'gray',
                                            ])
                                            ->icons([
                                                'en_attente' => 'heroicon-o-clock',
                                                'accepte' => 'heroicon-o-check-circle',
                                                'refuse' => 'heroicon-o-x-circle',
                                                'en_cours' => 'heroicon-o-arrows-right-left',
                                                'termine' => 'heroicon-o-check-badge',
                                            ])
                                            ->inline()
                                            ->default('en_attente')
                                            ->required(),
                                    ]),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Dates')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                DateTimePicker::make('date_demande')
                                    ->label('Date de la demande')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),

                                DateTimePicker::make('date_traitement')
                                    ->label('Date de traitement')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),

                                DateTimePicker::make('date_recuperation')
                                    ->label('Date de récupération')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),
                            ]),

                        Section::make('Aperçu')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                TextInput::make('client_nom')
                                    ->label('Client')
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => $get('client_nom') ?? '-'),

                                TextInput::make('montant_total')
                                    ->label('Montant total')
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => number_format($get('montant_total') ?? 0, 2).' €'),
                            ]),
                    ]),

                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Produits retournés')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Repeater::make('lignes')
                                    ->label('Articles')
                                    ->relationship('lignes')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Select::make('ligne_commande_id')
                                                    ->label('Produit')
                                                    ->relationship('ligneCommande', 'nom_produit')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),

                                                TextInput::make('quantite')
                                                    ->label('Quantité')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1)
                                                    ->default(1),

                                                Select::make('etat')
                                                    ->label('État du produit')
                                                    ->options([
                                                        'conforme' => 'Conforme',
                                                        'defectueux' => 'Défectueux',
                                                        'endommage' => 'Endommagé',
                                                        'incomplet' => 'Incomplet',
                                                    ])
                                                    ->required()
                                                    ->default('conforme'),

                                                TextInput::make('montant')
                                                    ->label('Montant à rembourser')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->prefix('€'),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->addActionLabel('Ajouter un produit')
                                    ->defaultItems(0)
                                    ->collapsible(),
                            ]),

                        Section::make('Commentaires et documents')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Textarea::make('commentaire')
                                    ->label('Commentaire')
                                    ->rows(3)
                                    ->placeholder('Informations supplémentaires sur le retour...'),

                                KeyValue::make('documents')
                                    ->label('Documents joints')
                                    ->keyLabel('Type')
                                    ->valueLabel('URL')
                                    ->addActionLabel('Ajouter un document')
                                    ->helperText('Photos, justificatifs, etc.'),

                                KeyValue::make('metadata')
                                    ->label('Métadonnées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter'),
                            ]),
                    ]),
            ]);
    }
}
