<?php

namespace App\Filament\Vendeur\Resources\Brands\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Tabs::make('Brand')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations générales')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Group::make()
                                    ->columnSpan(2)
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([

                                                TextInput::make('name')
                                                    ->label('Nom de la marque')
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

                                                TextInput::make('email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->prefixIcon('heroicon-m-envelope'),

                                            ]),

                                    ]),

                                Group::make()
                                    ->columnSpan(1)
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                TextInput::make('phone')
                                                    ->label('Téléphone')
                                                    ->tel()
                                                    ->prefixIcon('heroicon-m-phone'),
                                                ColorPicker::make('color')
                                                    ->label('Couleur principale')
                                                    ->helperText('Couleur associée à la marque'),
                                                TextInput::make('sort_order')
                                                    ->label('Ordre d\'affichage')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->step(1)
                                                    ->helperText('Plus le chiffre est petit, plus la marque apparaît en haut'),

                                                ToggleButtons::make('is_active')
                                                    ->label('Statut')
                                                    ->options([
                                                        true => 'Actif',
                                                        false => 'Inactif',
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
                                                    ->default(true),

                                                ToggleButtons::make('is_featured')
                                                    ->label('À la une')
                                                    ->options([
                                                        true => 'Oui',
                                                        false => 'Non',
                                                    ])
                                                    ->colors([
                                                        true => 'warning',
                                                        false => 'gray',
                                                    ])
                                                    ->icons([
                                                        true => 'heroicon-o-star',
                                                        false => 'heroicon-o-x-mark',
                                                    ])
                                                    ->inline()
                                                    ->default(false),
                                            ]),
                                        Textarea::make('description')
                                            ->label('Description')
                                            ->rows(4)
                                            ->maxLength(1000)
                                            ->helperText('Description de la marque'),
                                    ]),
                            ]),

                        Tab::make('Images et médias')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Logo')
                                            ->schema([
                                                SpatieMediaLibraryFileUpload::make('logo')
                                                    ->label('Logo de la marque')
                                                    ->collection('logo')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->circleCropper()
                                                    ->disk('tenant')
                                                    ->directory('brands/logo')
                                                    ->maxSize(2048)
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                                                    ->helperText('Format recommandé: PNG ou SVG avec fond transparent, 200x200px'),
                                            ]),

                                        Section::make('Image de couverture')
                                            ->schema([
                                                SpatieMediaLibraryFileUpload::make('cover')
                                                    ->label('Bannière / Couverture')
                                                    ->collection('cover')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->disk('tenant')
                                                    ->directory('brands/cover')
                                                    ->maxSize(5120)
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->helperText('Format recommandé: 1920x400px'),
                                            ]),
                                    ]),

                                Section::make('Galerie d\'images')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('gallery')
                                            ->label('Images additionnelles')
                                            ->collection('gallery')
                                            ->multiple()
                                            ->image()
                                            ->imageEditor()
                                            ->disk('tenant')
                                            ->directory('brands/gallery')
                                            ->maxFiles(10)
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->helperText('Photos de la marque, ateliers, produits phares'),
                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('seo.title')
                                            ->label('Titre SEO')
                                            ->maxLength(60)
                                            ->helperText('Idéalement entre 50 et 60 caractères'),

                                        Textarea::make('seo.description')
                                            ->label('Description SEO')
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText('Idéalement entre 150 et 160 caractères'),
                                    ]),
                            ]),

                        // Tab::make('Réseaux sociaux')
                        //     ->icon('heroicon-o-share')
                        //     ->schema([
                        //         Repeater::make('social_links')
                        //             ->label('Liens vers les réseaux sociaux')
                        //             ->schema([
                        //                 Select::make('platform')
                        //                     ->label('Plateforme')
                        //                     ->options([
                        //                         'facebook' => 'Facebook',
                        //                         'instagram' => 'Instagram',
                        //                         'twitter' => 'Twitter',
                        //                         'linkedin' => 'LinkedIn',
                        //                         'youtube' => 'YouTube',
                        //                         'tiktok' => 'TikTok',
                        //                         'pinterest' => 'Pinterest',
                        //                     ])
                        //                     ->searchable()
                        //                     ->preload()
                        //                     ->required(),

                        //                 TextInput::make('url')
                        //                     ->label('URL')
                        //                     ->url()
                        //                     ->required(),
                        //             ])
                        //             ->columns(2)
                        //             ->default([])
                        //             ->addActionLabel('Ajouter un réseau social')
                        //             ->collapsible(),
                        //     ]),

                        // Tab::make('Métadonnées')
                        //     ->icon('heroicon-o-code-bracket')
                        //     ->schema([
                        //         KeyValue::make('metadata')
                        //             ->label('Métadonnées personnalisées')
                        //             ->keyLabel('Clé')
                        //             ->valueLabel('Valeur')
                        //             ->addActionLabel('Ajouter une métadonnée')
                        //             ->reorderable()
                        //             ->helperText('Informations supplémentaires (ex: fondation, siège social, etc.)'),
                        //     ]),
                    ]),
            ]);
    }
}
