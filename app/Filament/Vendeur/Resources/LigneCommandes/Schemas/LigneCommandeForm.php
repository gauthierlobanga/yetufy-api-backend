<?php

namespace App\Filament\Vendeur\Resources\LigneCommandes\Schemas;

use App\Models\Produit;
use App\Models\VarianteProduit;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class LigneCommandeForm
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
                        Grid::make(2)
                            ->schema([

                                Select::make('produit_id')
                                    ->label('Produit')
                                    ->relationship('produit', 'nom', function ($query) {
                                        return $query->select('id', 'nom', 'reference', 'prix_ttc');
                                    })
                                    ->searchable(['nom', 'reference', 'sku'])
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $produit = Produit::find($state);
                                            if ($produit) {
                                                $prixBase = $produit->prix_ttc;
                                                $set('prix_unitaire', $prixBase);

                                                $quantite = floatval($get('quantite') ?? 1);
                                                $taxe = floatval($get('taxe') ?? 0);
                                                $remise = floatval($get('remise') ?? 0);
                                                $prixTotal = ($prixBase * $quantite) + ($taxe * $quantite) - $remise;
                                                $set('prix_total', max(0, $prixTotal));

                                                $set('produit_reference', $produit->reference);
                                                $set('produit_prix_base', $prixBase);
                                            }
                                        }
                                    })
                                    ->helperText('Sélectionnez le produit commandé'),

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
                                                $prixBase = $variante->prix_actuel;
                                                $set('prix_unitaire', $prixBase);

                                                $quantite = floatval($get('quantite') ?? 1);
                                                $taxe = floatval($get('taxe') ?? 0);
                                                $remise = floatval($get('remise') ?? 0);
                                                $prixTotal = ($prixBase * $quantite) + ($taxe * $quantite) - $remise;
                                                $set('prix_total', max(0, $prixTotal));
                                            }
                                        }
                                    })
                                    ->helperText('Variante du produit (taille, couleur, etc.)'),

                                // Remplacer Placeholder par TextInput disabled
                                TextInput::make('produit_reference')
                                    ->label('Référence produit')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => $get('produit_reference') ?? '-'),

                                // Remplacer Placeholder par TextInput disabled
                                TextInput::make('produit_prix_base')
                                    ->label('Prix unitaire de base')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => $get('produit_prix_base')
                                        ? Number::currency($get('produit_prix_base'), 'EUR')
                                        : '-'),
                            ]),
                    ]),

                Section::make('Quantité et prix')
                    ->icon('heroicon-o-calculator')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('quantite')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->step(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $prixUnitaire = floatval($get('prix_unitaire') ?? 0);
                                        $taxe = floatval($get('taxe') ?? 0);
                                        $remise = floatval($get('remise') ?? 0);
                                        $prixTotal = ($prixUnitaire * $state) + ($taxe * $state) - $remise;
                                        $set('prix_total', max(0, $prixTotal));
                                    }),

                                TextInput::make('prix_unitaire')
                                    ->label('Prix unitaire HT')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $quantite = floatval($get('quantite') ?? 1);
                                        $taxe = floatval($get('taxe') ?? 0);
                                        $remise = floatval($get('remise') ?? 0);
                                        $prixTotal = ($state * $quantite) + ($taxe * $quantite) - $remise;
                                        $set('prix_total', max(0, $prixTotal));
                                    }),

                                TextInput::make('taxe')
                                    ->label('Taxe unitaire')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $quantite = floatval($get('quantite') ?? 1);
                                        $prixUnitaire = floatval($get('prix_unitaire') ?? 0);
                                        $remise = floatval($get('remise') ?? 0);
                                        $prixTotal = ($prixUnitaire * $quantite) + ($state * $quantite) - $remise;
                                        $set('prix_total', max(0, $prixTotal));
                                    }),

                                TextInput::make('remise')
                                    ->label('Remise (totale)')
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $quantite = floatval($get('quantite') ?? 1);
                                        $prixUnitaire = floatval($get('prix_unitaire') ?? 0);
                                        $taxe = floatval($get('taxe') ?? 0);
                                        $prixTotal = ($prixUnitaire * $quantite) + ($taxe * $quantite) - $state;
                                        $set('prix_total', max(0, $prixTotal));
                                    }),

                                TextInput::make('prix_total')
                                    ->label('Prix total TTC')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Section::make('Récapitulatif')
                    ->icon('heroicon-o-document-text')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                // Remplacer Placeholder par TextInput disabled
                                TextInput::make('recap_quantite')
                                    ->label('Quantité commandée')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => Number::format($get('quantite') ?? 0)),

                                // Remplacer Placeholder par TextInput disabled
                                TextInput::make('recap_prix_unitaire')
                                    ->label('Prix unitaire')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => Number::currency($get('prix_unitaire') ?? 0, 'EUR')),

                                // Remplacer Placeholder par TextInput disabled
                                TextInput::make('recap_taxe_totale')
                                    ->label('Taxe totale')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($get) {
                                        $quantite = floatval($get('quantite') ?? 0);
                                        $taxe = floatval($get('taxe') ?? 0);
                                        $taxeTotale = $quantite * $taxe;

                                        return Number::currency($taxeTotale, 'EUR');
                                    }),

                                // Remplacer Placeholder par TextInput disabled
                                TextInput::make('recap_remise')
                                    ->label('Remise appliquée')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => Number::currency($get('remise') ?? 0, 'EUR')),

                                // Remplacer Placeholder par TextInput disabled
                                TextInput::make('recap_prix_total')
                                    ->label('Total TTC')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => Number::currency($get('prix_total') ?? 0, 'EUR'))
                                    ->extraAttributes(['class' => 'font-bold text-primary']),
                            ]),
                    ]),

                Section::make('Options supplémentaires')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        KeyValue::make('options')
                            ->label('Options personnalisées')
                            ->keyLabel('Option')
                            ->valueLabel('Valeur')
                            ->keyPlaceholder('Ex: Personnalisation')
                            ->valuePlaceholder('Ex: Gravure "Jean"')
                            ->addActionLabel('Ajouter une option')
                            ->reorderable()
                            ->helperText('Options spéciales pour cette ligne de commande'),
                    ]),
            ]);
    }
}
