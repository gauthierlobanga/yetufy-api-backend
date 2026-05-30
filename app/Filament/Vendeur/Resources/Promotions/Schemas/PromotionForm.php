<?php

namespace App\Filament\Vendeur\Resources\Promotions\Schemas;

use App\Models\ProductCategory;
use App\Models\Produit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Informations générales')
                            ->icon('heroicon-o-ticket')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Informations générales')
                                            ->icon('heroicon-o-ticket')
                                            ->schema([

                                                TextInput::make('nom')
                                                    ->label('Nom de la promotion')
                                                    ->required()
                                                    ->maxLength(255),
                                                Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(3)
                                                    ->maxLength(500),
                                                SpatieMediaLibraryFileUpload::make('banner')
                                                    ->label('Bannière promotionnelle')
                                                    ->image()
                                                    ->collection('banner')
                                                    ->disk('public')
                                                    ->directory('promotions')
                                                    ->visibility('public')
                                                    ->imageEditor()
                                                    ->helperText('Image affichée dans la section promotion (format paysage recommandé)'),
                                                Toggle::make('est_active')
                                                    ->label('Promotion active')
                                                    ->inline(false)
                                                    ->default(true),
                                            ]),

                                        // Section pour gérer les coupons associés à cette promotion
                                        Section::make('Coupons promotionnels')
                                            ->icon('heroicon-o-gift')
                                            ->collapsible()
                                            ->schema([
                                                Repeater::make('coupons')
                                                    ->label('Coupons')
                                                    ->schema([
                                                        TextInput::make('code')->label('Code')->required(),
                                                        TextInput::make('discount')->label('Réduction (€)')->numeric()->required(),
                                                        TextInput::make('min_amount')->label('Montant minimum (€)')->numeric(),
                                                    ])
                                                    ->columns(3)
                                                    ->addActionLabel('Ajouter un coupon')
                                                    ->defaultItems(0)
                                                    ->collapsible(),
                                            ]),
                                        TextInput::make('code')
                                            ->label('Code promo')
                                            ->maxLength(50)
                                            ->unique(ignoreRecord: true)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $set('code', Str::upper(Str::slug($state)));
                                            })
                                            ->helperText('Code unique pour les promotions avec code'),

                                        Select::make('type')
                                            ->label('Type de promotion')
                                            ->options([
                                                'pourcentage' => 'Pourcentage',
                                                'montant_fixe' => 'Montant fixe',
                                                'livraison_offerte' => 'Livraison offerte',
                                            ])
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default('pourcentage')
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state === 'livraison_offerte') {
                                                    $set('valeur', 0);
                                                }
                                            }),

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
                                            ->prefix('€')
                                            ->helperText('Montant minimum pour activer la promotion'),

                                        TextInput::make('maximum_discount')
                                            ->label('Réduction maximale')
                                            ->numeric()
                                            ->step(0.01)
                                            ->prefix('€')
                                            ->helperText('Plafond de la réduction')
                                            ->visible(fn ($get) => $get('type') === 'pourcentage'),

                                        Toggle::make('cumulable')
                                            ->label('Cumulable avec autres promotions')
                                            ->inline(false)
                                            ->default(false),
                                    ]),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Validité')
                            ->icon('heroicon-o-calendar')
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

                                TextInput::make('utilisation_max')
                                    ->label('Utilisation maximale')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Nombre maximum d\'utilisations de la promotion'),

                                TextInput::make('utilisation_courante')
                                    ->label('Utilisations actuelles')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),
                            ]),
                    ]),

                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Restrictions')
                            ->icon('heroicon-o-shield-check')
                            ->collapsible()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TagsInput::make('produits_cibles')
                                            ->label('Produits ciblés')
                                            ->placeholder('Ajouter un produit')
                                            ->suggestions(fn () => Produit::pluck('nom', 'id')->toArray())
                                            ->helperText('Laissez vide pour tous les produits'),

                                        TagsInput::make('categories_cibles')
                                            ->label('Catégories ciblées')
                                            ->placeholder('Ajouter une catégorie')
                                            ->suggestions(fn () => ProductCategory::pluck('nom', 'id')->toArray()),
                                    ]),
                            ]),

                        Section::make('Métadonnées')
                            ->icon('heroicon-o-code-bracket')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
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
