<?php

namespace App\Filament\Vendeur\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Tabs::make('Category')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations générales')
                            ->icon('heroicon-o-folder')
                            ->schema([
                                Group::make()
                                    ->columnSpan(2)
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('nom')
                                                    ->label('Nom de la catégorie')
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
                                            ]),

                                        Grid::make(2)
                                            ->schema([

                                                Select::make('parente_id')
                                                    ->label('Catégorie parente')
                                                    ->relationship('parent', 'nom', function ($query) {
                                                        return $query->orderBy('nom', 'asc');
                                                    })
                                                    ->searchable(['nom'])
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('nom')
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                                        TextInput::make('slug')
                                                            ->required()
                                                            ->unique(),
                                                    ])
                                                    ->helperText('Laissez vide pour une catégorie racine'),
                                            ]),

                                        Textarea::make('description')
                                            ->label('Description')
                                            ->rows(4)
                                            ->maxLength(500),

                                        TextInput::make('short_description')
                                            ->label('Description courte')
                                            ->maxLength(200),
                                    ]),

                                Group::make()
                                    ->columnSpan(1)
                                    ->schema([
                                        Section::make('Visibilité')
                                            ->schema([
                                                ToggleButtons::make('est_active')
                                                    ->label('Catégorie active')
                                                    ->options([
                                                        true => 'Actif',
                                                        false => 'Inactif',
                                                    ])
                                                    ->colors([
                                                        true => 'success',
                                                        false => 'danger',
                                                    ])
                                                    ->icons([
                                                        true => 'heroicon-m-check-circle',
                                                        false => 'heroicon-m-x-circle',
                                                    ])
                                                    ->inline()
                                                    ->required()
                                                    ->default(true),

                                                ToggleButtons::make('is_featured')
                                                    ->label('À la une')
                                                    ->options([
                                                        true => 'Oui',
                                                        false => 'Non',
                                                    ])
                                                    ->inline()
                                                    ->default(false),

                                                ToggleButtons::make('show_in_menu')
                                                    ->label('Visible dans le menu')
                                                    ->options([
                                                        true => 'Oui',
                                                        false => 'Non',
                                                    ])
                                                    ->inline()
                                                    ->default(true),

                                                TextInput::make('order')
                                                    ->label('Ordre d\'affichage')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->step(1)
                                                    ->helperText('Plus le chiffre est petit, plus la catégorie apparaît en haut'),

                                                ColorPicker::make('color')
                                                    ->label('Couleur')
                                                    ->helperText('Couleur d\'accentuation pour la catégorie'),
                                            ])->columns(5),
                                    ]),
                            ]),

                        Tab::make('Images')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Image principale')
                                            ->schema([
                                                SpatieMediaLibraryFileUpload::make('image')
                                                    ->label('Image de la catégorie')
                                                    ->collection('image')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('categories/image')
                                                    ->maxSize(2048)
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                                                    ->helperText('Image d\'illustration de la catégorie'),
                                            ]),

                                        Section::make('Icône')
                                            ->schema([
                                                SpatieMediaLibraryFileUpload::make('icon')
                                                    ->label('Icône de la catégorie')
                                                    ->collection('icon')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->circleCropper()
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('categories/icon')
                                                    ->maxSize(1024)
                                                    ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])
                                                    ->helperText('Petite icône pour la navigation (recommandé: SVG)'),
                                            ]),
                                    ]),

                                Section::make('Bannière')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('banner')
                                            ->label('Bannière de la catégorie')
                                            ->collection('banner')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('categories/banner')
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->helperText('Bannière pour l\'en-tête de la catégorie'),
                                    ]),

                                Section::make('Galerie')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('gallery')
                                            ->label('Images additionnelles')
                                            ->collection('gallery')
                                            ->multiple()
                                            ->image()
                                            ->imageEditor()
                                            ->maxFiles(10)
                                            ->maxSize(5120)
                                            ->reorderable()
                                            ->appendFiles()
                                            ->panelLayout('grid')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('produits/categories/gallery')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->helperText('Images supplémentaires pour illustrer la catégorie'),

                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('Titre SEO')
                                            ->maxLength(60)
                                            ->helperText('Titre pour les moteurs de recherche (60 caractères max)')
                                            ->extraAttributes(['data-seo-counter' => true]),

                                        Textarea::make('seo_description')
                                            ->label('Description SEO')
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText('Description pour les moteurs de recherche (160 caractères max)')
                                            ->extraAttributes(['data-seo-counter' => true]),

                                        SpatieTagsInput::make('tags')
                                            ->label('Mots-clés SEO')
                                            ->placeholder('Ajouter un mot-clé')
                                            ->splitKeys(['Tab', ' ', ','])
                                            ->helperText('Mots-clés séparés par des virgules'),

                                        SpatieTagsInput::make('seo_keywords')
                                            ->label('Mots-clés SEO')
                                            ->placeholder('Ajouter un mot-clé')
                                            ->splitKeys(['Tab', ' ', ','])
                                            ->helperText('Mots-clés séparés par des virgules'),
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
                                    ->reorderable(),
                            ]),
                    ]),
            ]);
    }
}
