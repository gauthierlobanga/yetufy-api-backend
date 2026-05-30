<?php

namespace App\Filament\Vendeur\Resources\Commandes\Schemas;

use App\Models\Client;
use App\Models\Panier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class CommandeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Informations générales')
                    ->icon('heroicon-o-shopping-cart')
                    ->columnSpan(2)
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                Select::make('client_id')
                                    ->label('Client')
                                    ->relationship('client', 'nom', function ($query) {
                                        return $query->select('id', 'nom', 'prenom', 'email');
                                    })
                                    ->searchable(['nom', 'prenom', 'email'])
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $client = Client::find($state);
                                            if ($client) {
                                                $set('client_email', $client->email);
                                                $set('client_phone', $client->telephone);
                                            }
                                        }
                                    })
                                    ->helperText('Sélectionnez le client associé à cette commande'),

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

                                TextInput::make('numero_commande')
                                    ->label('Numéro de commande')
                                    ->required()
                                    ->maxLength(50)
                                    ->default(fn () => 'CMD-'.date('Ymd').'-'.strtoupper(uniqid()))
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Numéro unique généré automatiquement'),

                                Select::make('statut')
                                    ->label('Statut')
                                    ->options([
                                        'en_attente' => 'En attente',
                                        'en_cours' => 'En cours',
                                        'termine' => 'Terminée',
                                        'annule' => 'Annulée',
                                        'rejete' => 'Rejetée',
                                    ])
                                    ->default('en_attente')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state === 'termine') {
                                            $set('date_livraison', now());
                                        } elseif ($state === 'annule') {
                                            $set('date_annulation', now());
                                        }
                                    }),

                                Select::make('mode_paiement')
                                    ->label('Mode de paiement')
                                    ->options([
                                        'carte' => 'Carte bancaire',
                                        'paypal' => 'PayPal',
                                        'virement' => 'Virement bancaire',
                                        'cheque' => 'Chèque',
                                        'especes' => 'Espèces',
                                    ])
                                    ->searchable(),
                            ]),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Informations supplémentaires sur la commande...')
                            ->helperText('Notes internes ou instructions spéciales'),
                    ]),

                Section::make('Montants')
                    ->icon('heroicon-o-currency-euro')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('sous_total')
                                    ->label('Sous-total HT')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $taxe = floatval($get('taxe') ?? 0);
                                        $frais = floatval($get('frais_livraison') ?? 0);
                                        $set('total', $state + $taxe + $frais);
                                    }),

                                TextInput::make('taxe')
                                    ->label('Taxe (TVA)')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $subtotal = floatval($get('sous_total') ?? 0);
                                        $frais = floatval($get('frais_livraison') ?? 0);
                                        $set('total', $subtotal + $state + $frais);
                                    }),

                                TextInput::make('frais_livraison')
                                    ->label('Frais de livraison')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $subtotal = floatval($get('sous_total') ?? 0);
                                        $taxe = floatval($get('taxe') ?? 0);
                                        $set('total', $subtotal + $taxe + $state);
                                    }),

                                TextInput::make('total')
                                    ->label('Total TTC')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(),

                                // Remplacer Placeholder par TextInput disabled
                                TextInput::make('total_lettres')
                                    ->label('Montant en lettres')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($get) {
                                        $total = floatval($get('total') ?? 0);

                                        return Number::spell($total, 'fr');
                                    })
                                    ->visible(fn ($get) => $get('total') > 0),
                            ]),
                    ]),

                Section::make('Adresses')
                    ->icon('heroicon-o-map-pin')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('adresse_facturation_id')
                                    ->label('Adresse de facturation')
                                    ->relationship('adresseFacturation', 'adresse_complete')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Adresse pour la facture'),

                                Select::make('adresse_livraison_id')
                                    ->label('Adresse de livraison')
                                    ->relationship('adresseLivraison', 'adresse_complete')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Adresse pour la livraison'),
                            ]),
                    ]),

                Section::make('Dates importantes')
                    ->icon('heroicon-o-calendar')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('date_commande')
                                    ->label('Date de commande')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false),

                                DateTimePicker::make('date_paiement')
                                    ->label('Date de paiement')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false),

                                DateTimePicker::make('date_expedition')
                                    ->label("Date d'expédition")
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false),

                                DateTimePicker::make('date_livraison')
                                    ->label('Date de livraison')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false),

                                DateTimePicker::make('date_annulation')
                                    ->label("Date d'annulation")
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false)
                                    ->visible(fn ($get) => $get('statut') === 'annule'),
                            ]),
                    ]),

                Section::make('Métadonnées')
                    ->icon('heroicon-o-code-bracket')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('metadata')
                            ->label('Métadonnées JSON')
                            ->placeholder('{"key": "value"}')
                            ->helperText('Données supplémentaires au format JSON'),
                    ]),
            ]);
    }
}
