<?php

namespace App\Filament\Vendeur\Resources\Produits\Schemas;

// use App\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProduitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Tabs::make('Produit')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations générales')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Group::make()
                                    ->columnSpan(2)
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([

                                                TextInput::make('nom')
                                                    ->label('Nom du produit')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if (empty($state)) {
                                                            return;
                                                        }
                                                        $set('slug', Str::slug($state));
                                                    }),

                                                TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText('Identifiant unique pour l\'URL'),
                                                TextInput::make('reference')
                                                    ->label('Référence')
                                                    ->maxLength(100),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('sku')
                                                    ->label('SKU')
                                                    ->maxLength(100)
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText('Stock Keeping Unit'),

                                                TextInput::make('ean')
                                                    ->label('EAN')
                                                    ->maxLength(13)
                                                    ->helperText('Code-barres EAN-13'),

                                                Select::make('brand_id')
                                                    ->label('Marque')
                                                    ->relationship('brand', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                                        TextInput::make('slug')
                                                            ->required()
                                                            ->unique(),
                                                    ]),
                                            ]),

                                        Textarea::make('short_description')
                                            ->label('Description courte')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->helperText('Apparaît dans les listes de produits'),

                                        Textarea::make('description_longue')
                                            ->label('Description complète')
                                            ->rows(8)
                                            ->helperText('Description détaillée du produit'),

                                        Select::make('categories')
                                            ->label('Catégories')
                                            ->relationship('categories', 'nom')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->helperText('Sélectionnez les catégories du produit'),
                                    ]),

                                Group::make()
                                    ->columnSpan(1)
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Select::make('statut')
                                                    ->label('Statut')
                                                    ->options([
                                                        'brouillon' => 'Brouillon',
                                                        'publie' => 'Publié',
                                                        'out_of_stock' => 'Rupture de stock',
                                                        'discontinued' => 'Abandonné',
                                                    ])
                                                    ->searchable()
                                                    ->preload()
                                                    ->default('draft')
                                                    ->required(),

                                                DateTimePicker::make('published_at')
                                                    ->label('Date de publication')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y H:i'),

                                                DateTimePicker::make('scheduled_for')
                                                    ->label('Programmé pour')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y H:i'),

                                                DateTimePicker::make('expires_at')
                                                    ->label('Expire le')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y H:i'),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                ToggleButtons::make('is_featured')
                                                    ->label('Produit à la une')
                                                    ->boolean()
                                                    ->default(false)
                                                    ->inline(),

                                                ToggleButtons::make('is_new')
                                                    ->label('Nouveauté')
                                                    ->boolean()
                                                    ->default(false)
                                                    ->inline(),

                                                ToggleButtons::make('is_bestseller')
                                                    ->label('Meilleure vente')
                                                    ->boolean()
                                                    ->default(false)
                                                    ->inline(),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Prix et stock')
                            ->icon('heroicon-o-currency-euro')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        // Select::make('currency_id')
                                        //     ->label('Devise')
                                        //     ->relationship('currency', 'code')
                                        //     // ->required()
                                        //     ->default(1),

                                        TextInput::make('prix_ht')
                                            ->label('Prix HT')
                                            ->numeric()
                                            ->required()
                                            ->prefix('€')
                                            ->step(0.01),

                                        TextInput::make('prix_ttc')
                                            ->label('Prix TTC')
                                            ->numeric()
                                            ->required()
                                            ->prefix('€')
                                            ->step(0.01),

                                        ToggleButtons::make('is_deal_of_the_day')
                                            ->label('Deal du jour')
                                            ->boolean()
                                            ->default(false)
                                            ->inline()
                                            ->live(),

                                        TextInput::make('prix_promotion')
                                            ->label('Prix promotionnel')
                                            ->numeric()
                                            ->prefix('€')
                                            ->step(0.01)
                                            ->required(fn (callable $get) => $get('is_deal_of_the_day') === true) // obligatoire si deal activé
                                            ->helperText('Laissez vide si pas de promotion. Obligatoire pour un Deal du jour.'),

                                        DateTimePicker::make('expires_at')
                                            ->label('Expire le')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->required(fn (callable $get) => $get('is_deal_of_the_day') === true)
                                            ->helperText('Date de fin du Deal du jour.'),

                                        TextInput::make('quantite_stock')
                                            ->label('Quantité en stock')
                                            ->numeric()
                                            ->required()
                                            ->default(0)
                                            ->minValue(0),

                                        TextInput::make('seuil_alerte')
                                            ->label('Seuil d\'alerte')
                                            ->numeric()
                                            ->minValue(0)
                                            ->helperText('Notification quand le stock atteint ce seuil'),
                                    ]),

                                Fieldset::make('Dimensions et poids')
                                    ->label('Dimensions et poids')
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                TextInput::make('poids')
                                                    ->label('Poids (kg)')
                                                    ->numeric()
                                                    ->step(0.01),

                                                TextInput::make('hauteur')
                                                    ->label('Hauteur (cm)')
                                                    ->numeric()
                                                    ->step(0.1),

                                                TextInput::make('largeur')
                                                    ->label('Largeur (cm)')
                                                    ->numeric()
                                                    ->step(0.1),

                                                TextInput::make('profondeur')
                                                    ->label('Profondeur (cm)')
                                                    ->numeric()
                                                    ->step(0.1),

                                                TextInput::make('unite_mesure')
                                                    ->label('Unité de mesure')
                                                    ->default('cm'),
                                            ])->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Images et médias')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Images du produit')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('image_principale')
                                            ->label('Image principale du produit')
                                            ->collection('image_principale')
                                            ->image()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('products/main')
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->helperText('Cette image sera utilisée comme vignette principale dans les listes et la page produit'),

                                        SpatieMediaLibraryFileUpload::make('images')
                                            ->label('Galerie d\'images')
                                            ->collection('images')
                                            ->multiple()
                                            ->image()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('products/gallery')
                                            ->panelLayout('grid')
                                            ->imageEditor()
                                            ->reorderable()
                                            ->maxFiles(20)
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->helperText('Images du produit (ordre modifiable par glisser-déposer)')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Vidéos')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('videos')
                                            ->label('Vidéos du produit')
                                            ->collection('videos')
                                            ->multiple()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('products/videos')
                                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                            ->maxSize(51200) // 50MB
                                            ->helperText('Vidéos de présentation du produit (MP4, WebM)')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Documents')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('documents')
                                            ->label('Documents techniques')
                                            ->collection('documents')
                                            ->multiple()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('products/documents')
                                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                            ->maxSize(10240) // 10MB
                                            ->helperText('Manuels, fiches techniques, certificats')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Variantes et attributs')
                            ->icon('heroicon-o-bars-3')
                            ->schema([
                                Repeater::make('variantes')
                                    ->label('Variantes du produit')
                                    ->relationship('variantes')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('nom')
                                                    ->label('Nom de la variante')
                                                    ->required()
                                                    ->placeholder('Ex: Taille, Couleur'),

                                                TextInput::make('valeur')
                                                    ->label('Valeur')
                                                    ->required()
                                                    ->placeholder('Ex: M, Rouge'),

                                                TextInput::make('supplement_prix')
                                                    ->label('Supplément prix')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->prefix('€')
                                                    ->step(0.01),

                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('stock')
                                                    ->label('Stock')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0),

                                                TextInput::make('sku_variante')
                                                    ->label('SKU')
                                                    ->maxLength(100),
                                            ]),
                                        KeyValue::make('attributs')
                                            ->label('Attributs supplémentaires')
                                            ->keyLabel('Propriété')
                                            ->valueLabel('Valeur')
                                            ->addActionLabel('Ajouter un attribut')
                                            ->dehydrateStateUsing(fn ($state) => is_array($state)
                                                ? collect($state)
                                                    ->filter(fn ($value, $key) => $key !== '' && $key !== null && $value !== '' && $value !== null)
                                                    ->toArray()
                                                : []
                                            )
                                            ->afterStateHydrated(fn ($state) => is_array($state)
                                                ? collect($state)
                                                    ->filter(fn ($value, $key) => $key !== '' && $key !== null)
                                                    ->toArray()
                                                : []
                                            )
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->addActionLabel('Ajouter une variante')
                                    ->defaultItems(0)
                                    ->collapsible(),

                                // KeyValue::make('attributes')
                                //     ->label('Attributs personnalisés')
                                //     ->keyLabel('Attribut')
                                //     ->valueLabel('Valeur')
                                //     ->addActionLabel('Ajouter un attribut')
                                //     ->dehydrateStateUsing(fn ($state) => is_array($state)
                                //         ? collect($state)
                                //             ->filter(fn ($value, $key) => $key !== '' && $key !== null && $value !== '' && $value !== null)
                                //             ->toArray()
                                //         : []
                                //     )
                                //     ->afterStateHydrated(fn ($state) => is_array($state)
                                //         ? collect($state)
                                //             ->filter(fn ($value, $key) => $key !== '' && $key !== null)
                                //             ->toArray()
                                //         : []
                                //     )
                                //     ->helperText('Attributs supplémentaires du produit (ex: Matière: Coton, Garantie: 2 ans)'),
                            ]),
                        Tab::make('Attributs supplémentaires')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->columnSpanFull()
                            ->schema([
                                KeyValue::make('metadata')
                                    ->label('Métadonnées personnalisées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter')
                                    ->reorderable()
                                    ->dehydrateStateUsing(fn ($state) => is_array($state)
                                        ? collect($state)
                                            ->filter(fn ($value, $key) => $key !== '' && $key !== null && $value !== '' && $value !== null)
                                            ->toArray()
                                        : []
                                    )
                                    ->afterStateHydrated(fn ($state) => is_array($state)
                                        ? collect($state)
                                            ->filter(fn ($value, $key) => $key !== '' && $key !== null)
                                            ->toArray()
                                        : []
                                    ),
                            ]),

                        Tab::make('SEO et tags')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('Titre SEO')
                                            ->maxLength(60)
                                            ->helperText('Idéalement entre 50 et 60 caractères'),
                                    ]),

                                Section::make('Tags')
                                    ->schema([

                                        Textarea::make('seo_description')
                                            ->label('Description SEO')
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText('Idéalement entre 150 et 160 caractères'),

                                        SpatieTagsInput::make('tags')
                                            ->label('Tags du produit')
                                            ->placeholder('Ajouter un tag')
                                            ->splitKeys(['Tab', ' ', ','])
                                            ->helperText('Tags pour catégoriser le produit'),
                                    ]),
                            ]),

                        Tab::make('Métadonnées')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                KeyValue::make('metadata')
                                    ->label('Métadonnées personnalisées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter')
                                    ->reorderable()
                                    ->dehydrateStateUsing(fn ($state) => is_array($state) ? array_filter($state, fn ($key, $value) => $key !== '' && $value !== '', ARRAY_FILTER_USE_BOTH) : $state)
                                    ->afterStateHydrated(fn ($state) => is_array($state) ? array_filter($state, fn ($key) => $key !== '', ARRAY_FILTER_USE_KEY) : $state),
                            ]),
                    ]),
            ]);
    }
}
