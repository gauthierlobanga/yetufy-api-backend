<?php

namespace App\Filament\Vendeur\Resources\ItemPaniers\Schemas;

use App\Models\Produit;
use App\Models\VarianteProduit;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ItemPanierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations du produit')
                    ->icon('heroicon-o-shopping-bag')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([

                                Select::make('panier_id')
                                    ->label('Panier')
                                    ->relationship('panier', 'id', fn ($query) => $query->select('id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

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
                                                $set('prix_unitaire', $produit->prix_ttc);
                                                $quantite = floatval($get('quantite') ?? 1);
                                                $set('prix_total', $produit->prix_ttc * $quantite);
                                                $set('produit_reference', $produit->reference);
                                            }
                                        }
                                    }),

                                Select::make('variante_produit_id')
                                    ->label('Variante')
                                    ->relationship('variante', 'nom', function ($query, $get) {
                                        $produitId = $get('produit_id');
                                        if ($produitId) {
                                            $query->where('produit_id', $produitId);
                                        }

                                        return $query;
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $variante = VarianteProduit::find($state);
                                            if ($variante) {
                                                $set('prix_unitaire', $variante->prix_actuel);
                                                $quantite = floatval($get('quantite') ?? 1);
                                                $set('prix_total', $variante->prix_actuel * $quantite);
                                            }
                                        }
                                    }),
                            ]),
                    ]),

                Section::make('Quantité et prix')
                    ->icon('heroicon-o-calculator')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('quantite')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $prixUnitaire = floatval($get('prix_unitaire') ?? 0);
                                        $set('prix_total', $prixUnitaire * $state);
                                    }),

                                TextInput::make('prix_unitaire')
                                    ->label('Prix unitaire HT')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $quantite = floatval($get('quantite') ?? 1);
                                        $set('prix_total', $state * $quantite);
                                    }),

                                TextInput::make('prix_total')
                                    ->label('Total TTC')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('taxe_unitaire')
                                    ->label('Taxe unitaire')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€'),

                                TextInput::make('remise_unitaire')
                                    ->label('Remise unitaire')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€'),
                            ]),
                    ]),

                Section::make('Options et personnalisation')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columnSpanFull()
                    ->schema([
                        KeyValue::make('options_selectionnees')
                            ->label('Options sélectionnées')
                            ->keyLabel('Option')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter une option')
                            ->reorderable(),

                        KeyValue::make('personnalisation')
                            ->label('Personnalisation')
                            ->keyLabel('Champ')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter une personnalisation')
                            ->reorderable(),
                    ]),
            ]);
    }
}
