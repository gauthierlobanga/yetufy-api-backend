<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // Colonne principale
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Tabs::make('Category')
                            ->tabs([
                                Tab::make('Informations')
                                    ->icon('heroicon-m-information-circle')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('nom')
                                                    ->label('Nom')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                        if (! $get('slug') && $state) {
                                                            $set('slug', Str::slug($state));
                                                        }
                                                    }),

                                                TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText('Identifiant unique pour l\'URL'),
                                            ]),

                                        Textarea::make('description')
                                            ->label('Description')
                                            ->rows(4)
                                            ->maxLength(500)
                                            ->helperText('Description de la catégorie'),

                                        Grid::make(2)
                                            ->schema([
                                                ColorPicker::make('color')
                                                    ->label('Couleur')
                                                    ->helperText('Couleur d\'accentuation'),

                                                Select::make('parent_id')
                                                    ->label('Catégorie parente')
                                                    ->relationship('parent', 'nom', function ($query) {
                                                        // Forcer le tri sur la colonne 'nom' (varchar)
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
                                    ]),

                                Tab::make('SEO')
                                    ->icon('heroicon-m-magnifying-glass')
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->label('Titre SEO')
                                            ->maxLength(60)
                                            ->helperText('Titre pour les moteurs de recherche (60 caractères max)')
                                            ->placeholder(fn (Get $get): string => $get('nom') ?? ''),

                                        Textarea::make('meta_description')
                                            ->label('Description SEO')
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText('Description pour les moteurs de recherche (160 caractères max)')
                                            ->placeholder(fn (Get $get): string => $get('description') ?? ''),

                                        TagsInput::make('meta_keywords')
                                            ->label('Mots-clés SEO')
                                            ->placeholder('Nouveau mot-clé')
                                            ->splitKeys(['Tab', ' ', ','])
                                            ->helperText('Mots-clés séparés par des virgules'),
                                    ]),
                            ]),
                    ]),

                // Sidebar droite
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Visibilité')
                            ->icon('heroicon-m-eye')
                            ->schema([
                                ToggleButtons::make('est_active')
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
                                        true => 'heroicon-m-check-circle',
                                        false => 'heroicon-m-x-circle',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->default(true),

                                Toggle::make('est_visible_dans_menu')
                                    ->label('Visible dans le menu')
                                    ->inline(false)
                                    ->helperText('Afficher cette catégorie dans la navigation'),
                            ]),

                        Section::make('Organisation')
                            ->icon('heroicon-m-arrows-up-down')
                            ->schema([
                                TextInput::make('ordre')
                                    ->label('Ordre d\'affichage')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(999)
                                    ->default(0)
                                    ->step(1)
                                    ->helperText('Plus le chiffre est petit, plus la catégorie apparaît en haut'),
                            ]),

                        Section::make('Statistiques')
                            ->icon('heroicon-m-chart-bar')
                            ->collapsed()
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('posts_count')
                                            ->label('Nombre de posts')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(fn ($record): int => $record?->posts()->count() ?? 0),

                                        TextInput::make('enfants_count')
                                            ->label('Sous-catégories')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(fn ($record): int => $record?->enfants()->count() ?? 0),
                                    ]),
                            ]),

                        Section::make('Métadonnées avancées')
                            ->icon('heroicon-m-cog')
                            ->collapsed()
                            ->schema([
                                KeyValue::make('metadata')
                                    ->label('Métadonnées personnalisées')
                                    ->keyLabel('Property name')
                                    ->keyPlaceholder('Property name')
                                    ->valueLabel('Property value')
                                    ->valuePlaceholder('Property value')
                                    ->addActionLabel('Ajouter')
                                    ->reorderable()
                                    ->columnSpanFull()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? [])
                                    ->formatStateUsing(fn ($state) => is_array($state) ? $state : []),

                            ]),
                    ]),
            ]);
    }
}
