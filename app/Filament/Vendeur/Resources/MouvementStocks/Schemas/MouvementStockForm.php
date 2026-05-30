<?php

namespace App\Filament\Vendeur\Resources\MouvementStocks\Schemas;

use App\Models\Produit;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MouvementStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([

                        Section::make('Informations du mouvement')
                            ->icon('heroicon-o-arrows-right-left')
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                                        Select::make('produit_id')
                                            ->label('Produit')
                                            ->relationship('produit', 'nom')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state) {
                                                    $produit = Produit::find($state);
                                                    if ($produit) {
                                                        $set('produit_sku', $produit->sku);
                                                        $set('produit_stock_actuel', $produit->quantite_stock);
                                                        $set('produit_prix', $produit->prix_ttc);
                                                    }
                                                }

                                                $quantite = intval($get('quantite') ?? 0);
                                                if ($quantite < 0 && abs($quantite) > ($get('produit_stock_actuel') ?? 0)) {
                                                    $set('stock_suffisant', false);
                                                } else {
                                                    $set('stock_suffisant', true);
                                                }
                                            }),

                                        TextInput::make('produit_sku')
                                            ->label('SKU')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($get) => $get('produit_sku') ?? '-'),

                                        Select::make('type')
                                            ->label('Type de mouvement')
                                            ->options([
                                                'entree' => 'Entrée en stock',
                                                'sortie' => 'Sortie de stock',
                                                'ajustement' => 'Ajustement',
                                                'transfert' => 'Transfert',
                                            ])
                                            ->required()
                                            ->default('entree')
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state === 'entree') {
                                                    $set('quantite', abs(intval($set('quantite') ?? 0)));
                                                } elseif ($state === 'sortie') {
                                                    $set('quantite', -abs(intval($set('quantite') ?? 0)));
                                                }
                                            }),

                                        TextInput::make('quantite')
                                            ->label('Quantité')
                                            ->numeric()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $type = $get('type');
                                                if ($type === 'entree') {
                                                    $set('quantite', abs($state));
                                                } elseif ($type === 'sortie') {
                                                    $set('quantite', -abs($state));
                                                }

                                                if ($type === 'sortie' && abs($state) > ($get('produit_stock_actuel') ?? 0)) {
                                                    $set('stock_suffisant', false);
                                                    $set('message_erreur', 'Stock insuffisant !');
                                                } else {
                                                    $set('stock_suffisant', true);
                                                    $set('message_erreur', null);
                                                }
                                            }),

                                        TextInput::make('message_erreur')
                                            ->label('')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($get) => $get('message_erreur') ?? '')
                                            ->visible(fn ($get) => ! $get('stock_suffisant'))
                                            ->extraAttributes(['class' => 'text-danger-600 font-medium']),

                                        Select::make('entrepot_id')
                                            ->label('Entrepôt')
                                            ->relationship('entrepot', 'nom')
                                            ->searchable()
                                            ->preload(),

                                        Select::make('inventaire_id')
                                            ->label('Inventaire associé')
                                            ->relationship('inventaire', 'reference')
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),
                                    ]),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Valeurs')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                // ✅ Version corrigée avec gestion des valeurs non numériques
                                TextInput::make('produit_prix')
                                    ->label('Prix unitaire')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($get) {
                                        $prix = $get('produit_prix');
                                        if (empty($prix) || ! is_numeric($prix)) {
                                            return '0,00 €';
                                        }

                                        return number_format(floatval($prix), 2, ',', ' ').' €';
                                    }),

                                // ✅ Version corrigée avec gestion des valeurs non numériques
                                TextInput::make('valeur_totale')
                                    ->label('Valeur totale')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($get) {
                                        $prix = $get('produit_prix');
                                        $quantite = $get('quantite');

                                        if (empty($prix) || ! is_numeric($prix)) {
                                            $prix = 0;
                                        } else {
                                            $prix = floatval($prix);
                                        }

                                        if (empty($quantite) || ! is_numeric($quantite)) {
                                            $quantite = 0;
                                        } else {
                                            $quantite = abs(intval($quantite));
                                        }

                                        $valeur = $prix * $quantite;

                                        return number_format($valeur, 2, ',', ' ').' €';
                                    })
                                    ->extraAttributes(['class' => 'text-success-600 font-semibold']),

                                // ✅ Version corrigée avec gestion des valeurs non numériques
                                TextInput::make('produit_stock_actuel')
                                    ->label('Stock actuel')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($get) {
                                        $stock = $get('produit_stock_actuel');
                                        if (empty($stock) || ! is_numeric($stock)) {
                                            return '0';
                                        }

                                        return number_format(intval($stock), 0, ',', ' ');
                                    }),

                                // ✅ Version corrigée avec gestion des valeurs non numériques
                                TextInput::make('stock_apres')
                                    ->label('Stock après mouvement')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($get) {
                                        $stockActuel = $get('produit_stock_actuel');
                                        $quantite = $get('quantite');

                                        if (empty($stockActuel) || ! is_numeric($stockActuel)) {
                                            $stockActuel = 0;
                                        } else {
                                            $stockActuel = intval($stockActuel);
                                        }

                                        if (empty($quantite) || ! is_numeric($quantite)) {
                                            $quantite = 0;
                                        } else {
                                            $quantite = intval($quantite);
                                        }

                                        $stockApres = $stockActuel + $quantite;

                                        $color = $stockApres < 0 ? 'text-danger-600' : ($stockApres < 10 ? 'text-warning-600' : 'text-success-600');

                                        return [
                                            'html' => number_format($stockApres, 0, ',', ' '),
                                            'class' => "font-semibold {$color}",
                                        ];
                                    })
                                    ->formatStateUsing(function ($get) {
                                        $stockActuel = $get('produit_stock_actuel');
                                        $quantite = $get('quantite');

                                        if (empty($stockActuel) || ! is_numeric($stockActuel)) {
                                            $stockActuel = 0;
                                        } else {
                                            $stockActuel = intval($stockActuel);
                                        }

                                        if (empty($quantite) || ! is_numeric($quantite)) {
                                            $quantite = 0;
                                        } else {
                                            $quantite = intval($quantite);
                                        }

                                        $stockApres = $stockActuel + $quantite;

                                        return number_format($stockApres, 0, ',', ' ');
                                    })
                                    ->extraAttributes(function ($get) {
                                        $stockActuel = $get('produit_stock_actuel');
                                        $quantite = $get('quantite');

                                        if (empty($stockActuel) || ! is_numeric($stockActuel)) {
                                            $stockActuel = 0;
                                        } else {
                                            $stockActuel = intval($stockActuel);
                                        }

                                        if (empty($quantite) || ! is_numeric($quantite)) {
                                            $quantite = 0;
                                        } else {
                                            $quantite = intval($quantite);
                                        }

                                        $stockApres = $stockActuel + $quantite;
                                        $color = $stockApres < 0 ? 'text-danger-600' : ($stockApres < 10 ? 'text-warning-600' : 'text-success-600');

                                        return ['class' => "font-semibold {$color}"];
                                    }),
                            ]),
                    ]),

                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Informations complémentaires')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('reference')
                                    ->label('Référence externe')
                                    ->maxLength(255)
                                    ->placeholder('N° de commande, facture, bon de livraison...')
                                    ->helperText('Document associé à ce mouvement'),

                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(3)
                                    ->placeholder('Informations supplémentaires sur le mouvement'),

                                DateTimePicker::make('date_mouvement')
                                    ->label('Date du mouvement')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false),

                                KeyValue::make('metadata')
                                    ->label('Métadonnées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter')
                                    ->reorderable()
                                    ->helperText('Informations supplémentaires'),
                            ]),
                    ]),
            ]);
    }
}
