<?php

namespace App\Filament\Vendeur\Resources\Coupons\Schemas;

use App\Models\ProductCategory;
use App\Models\Produit;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Informations générales')
                    ->icon('heroicon-o-ticket')
                    ->columnSpan(2)
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextInput::make('code')
                                    ->label('Code promo')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('code', Str::upper(Str::slug($state)));
                                    })
                                    ->helperText('Code unique que les clients saisiront'),

                                TextInput::make('nom')
                                    ->label('Nom du coupon')
                                    ->maxLength(255)
                                    ->placeholder('Ex: Soldes d\'été'),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Validité')
                            ->icon('heroicon-o-calendar')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        DateTimePicker::make('date_debut')
                                            ->label('Date de début')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->seconds(false),

                                        DateTimePicker::make('date_fin')
                                            ->label('Date de fin')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->seconds(false),
                                    ]),
                            ]),
                        Section::make('Restrictions')
                            ->icon('heroicon-o-shield-check')
                            ->columnSpanFull()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TagsInput::make('produits_applicables')
                                            ->label('Produits applicables')
                                            ->placeholder('Sélectionner des produits')
                                            ->suggestions(fn () => Produit::pluck('nom', 'id')->toArray())
                                            ->helperText('Laissez vide pour tous les produits'),

                                        TagsInput::make('produits_exclus')
                                            ->label('Produits exclus')
                                            ->placeholder('Sélectionner des produits')
                                            ->suggestions(fn () => Produit::pluck('nom', 'id')->toArray()),

                                        TagsInput::make('categories_applicables')
                                            ->label('Catégories applicables')
                                            ->placeholder('Sélectionner des catégories')
                                            ->suggestions(fn () => ProductCategory::pluck('nom', 'id')->toArray()),

                                        TagsInput::make('utilisateurs_applicables')
                                            ->label('Utilisateurs applicables')
                                            ->placeholder('Sélectionner des utilisateurs')
                                            ->suggestions(fn () => User::pluck('name', 'id')->toArray()),
                                    ]),
                                Section::make('Métadonnées')
                                    ->icon('heroicon-o-code-bracket')
                                    ->columnSpanFull()
                                    ->schema([
                                        KeyValue::make('metadata')
                                            ->label('Métadonnées personnalisées')
                                            ->keyLabel('Clé')
                                            ->valueLabel('Valeur')
                                            ->addActionLabel('Ajouter'),
                                    ]),
                            ]),
                    ]),

                Section::make('Type et valeur')
                    ->icon('heroicon-o-calculator')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                ToggleButtons::make('type')
                                    ->label('Type de réduction')
                                    ->options([
                                        'pourcentage' => 'Pourcentage',
                                        'montant_fixe' => 'Montant fixe',
                                        'livraison_offerte' => 'Livraison offerte',
                                    ])
                                    ->colors([
                                        'pourcentage' => 'primary',
                                        'montant_fixe' => 'success',
                                        'livraison_offerte' => 'warning',
                                    ])
                                    ->icons([
                                        'pourcentage' => Heroicon::OutlinedPercentBadge,
                                        'montant_fixe' => 'heroicon-o-currency-euro',
                                        'livraison_offerte' => 'heroicon-o-truck',
                                    ])
                                    ->inline()
                                    ->default('pourcentage')
                                    ->required(),

                                TextInput::make('valeur')
                                    ->label('Valeur')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix(fn ($get) => $get('type') === 'pourcentage' ? '%' : '€'),

                                TextInput::make('minimum_panier')
                                    ->label('Montant minimum du panier')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€'),

                                TextInput::make('maximum_discount')
                                    ->label('Réduction maximale')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->visible(fn ($get) => $get('type') === 'pourcentage'),

                                Toggle::make('free_shipping')
                                    ->label('Livraison offerte')
                                    ->inline(false)
                                    ->default(false),
                            ]),
                        Section::make('Limites d\'utilisation')
                            ->icon('heroicon-o-chart-bar')
                            ->columnSpan(1)
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('utilisation_max')
                                            ->label('Utilisation maximale totale')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Nombre maximum d\'utilisations du coupon'),

                                        TextInput::make('utilisation_par_utilisateur')
                                            ->label('Utilisation par utilisateur')
                                            ->numeric()
                                            ->minValue(1)
                                            ->helperText('Nombre maximum par client'),

                                        TextInput::make('total_utilise')
                                            ->label('Nombre d\'utilisations')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->default(0),

                                        ToggleButtons::make('premiere_commande')
                                            ->label('Réservé aux premières commandes')
                                            ->options([
                                                true => 'Oui',
                                                false => 'Non',
                                            ])
                                            ->inline()
                                            ->default(false),

                                        ToggleButtons::make('cumulable')
                                            ->label('Cumulable avec autres promotions')
                                            ->options([
                                                true => 'Oui',
                                                false => 'Non',
                                            ])
                                            ->inline()
                                            ->default(false),

                                        ToggleButtons::make('est_actif')
                                            ->label('Coupon actif')
                                            ->options([
                                                true => 'Actif',
                                                false => 'Inactif',
                                            ])
                                            ->colors([
                                                true => 'success',
                                                false => 'danger',
                                            ])
                                            ->inline()
                                            ->default(true),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
