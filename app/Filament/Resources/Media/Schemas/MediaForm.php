<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // Colonne principale - Informations du fichier
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Fichier')
                            ->icon('heroicon-o-document')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        // Aperçu de l'image (si c'est une image)
                                        FileUpload::make('file')
                                            ->label('Remplacer le fichier')
                                            ->disk('public')
                                            ->directory('media')
                                            ->image()
                                            ->maxSize(10240)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                                            ->helperText('Laissez vide pour conserver le fichier actuel')
                                            ->columnSpanFull(),

                                        TextInput::make('name')
                                            ->label('Nom du fichier')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('ex: photo-profil-2024')
                                            ->helperText('Nom d\'affichage du fichier'),

                                        TextInput::make('file_name')
                                            ->label('Nom original')
                                            ->required()
                                            ->maxLength(255)
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->helperText('Nom original du fichier uploadé'),

                                        TextInput::make('order_column')
                                            ->label("Ordre d'affichage")
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(9999)
                                            ->step(1)
                                            ->helperText('Plus le chiffre est petit, plus l\'élément apparaît en haut'),
                                    ]),
                            ]),

                        Section::make('Propriétés techniques')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('mime_type')
                                            ->label('Type MIME')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->helperText('Type de fichier détecté automatiquement'),

                                        TextInput::make('size')
                                            ->label('Taille (octets)')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->helperText('Taille du fichier en octets'),

                                        TextInput::make('disk')
                                            ->label('Disque de stockage')
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('uuid')
                                            ->label('UUID')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->copyable(),
                                    ]),
                            ]),
                    ]),

                // Colonne latérale - Métadonnées
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Classification')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Select::make('collection_name')
                                    ->label('Collection')
                                    ->options([
                                        'featured' => 'À la une',
                                        'gallery' => 'Galerie',
                                        'attachments' => 'Pièces jointes',
                                        'avatar' => 'Avatar',
                                        'documents' => 'Documents',
                                        'videos' => 'Vidéos',
                                        'default' => 'Par défaut',
                                    ])
                                    ->required()
                                    ->default('default')
                                    ->helperText('Catégorie de rangement du média')
                                    ->searchable(),

                                Select::make('model_type')
                                    ->label('Modèle associé')
                                    ->options(function () {
                                        $models = [
                                            'App\Models\Post' => 'Post',
                                            'App\Models\Product' => 'Product',
                                            'App\Models\User' => 'User',
                                            'App\Models\Category' => 'Category',
                                            'App\Models\Brand' => 'Brand',
                                        ];

                                        return $models;
                                    })
                                    ->searchable()
                                    ->helperText('Type d\'entité associée à ce média'),

                                TextInput::make('model_id')
                                    ->label('ID du modèle')
                                    ->uuid()
                                    ->helperText('ID de l\'entité associée (ex: ID du post, ID du produit)')
                                    ->visible(fn ($get) => ! empty($get('model_type'))),
                            ]),

                        Section::make('Conversions')
                            ->icon('heroicon-o-arrow-path-rounded-square')
                            ->schema([
                                ToggleButtons::make('generate_conversions')
                                    ->label('Générer les conversions')
                                    ->options([
                                        true => 'Oui',
                                        false => 'Non',
                                    ])
                                    ->colors([
                                        true => 'success',
                                        false => 'danger',
                                    ])
                                    ->inline()
                                    ->default(true)
                                    ->helperText('Génère automatiquement les versions optimisées (thumb, small, medium, large)'),

                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('conversion_thumb')
                                            ->label('Miniature (100x100)')
                                            ->default(true)
                                            ->visible(fn ($get) => $get('generate_conversions')),

                                        Toggle::make('conversion_small')
                                            ->label('Petite (300x300)')
                                            ->default(true)
                                            ->visible(fn ($get) => $get('generate_conversions')),

                                        Toggle::make('conversion_medium')
                                            ->label('Moyenne (800x800)')
                                            ->default(true)
                                            ->visible(fn ($get) => $get('generate_conversions')),

                                        Toggle::make('conversion_large')
                                            ->label('Grande (1200x1200)')
                                            ->default(false)
                                            ->visible(fn ($get) => $get('generate_conversions')),
                                    ])
                                    ->visible(fn ($get) => $get('generate_conversions')),
                            ]),

                        Section::make('Métadonnées avancées')
                            ->icon('heroicon-o-code-bracket')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                KeyValue::make('custom_properties')
                                    ->label('Propriétés personnalisées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->keyPlaceholder('ex: alt_text')
                                    ->valuePlaceholder('ex: Description de l\'image')
                                    ->addActionLabel('Ajouter une propriété')
                                    ->reorderable()
                                    ->columnSpanFull()
                                    ->helperText('Métadonnées additionnelles (texte alternatif, crédits, etc.)'),

                                KeyValue::make('manipulations')
                                    ->label('Manipulations')
                                    ->keyLabel('Type')
                                    ->valueLabel('Valeur')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record) => $record !== null),

                                KeyValue::make('responsive_images')
                                    ->label('Images responsives')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record) => $record !== null),
                            ]),
                    ]),

                // Informations système (lecture seule)
                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Informations système')
                            ->icon('heroicon-o-information-circle')
                            ->collapsible()
                            ->collapsed()
                            ->visible(fn ($record) => $record !== null)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        DateTimePicker::make('created_at')
                                            ->label('Créé le')
                                            ->disabled()
                                            ->dehydrated(false),
                                        // ->formatStateUsing(fn ($state) => $state?->format('d/m/Y H:i:s')),

                                        DateTimePicker::make('updated_at')
                                            ->label('Modifié le')
                                            ->disabled()
                                            ->dehydrated(false),
                                        // ->formatStateUsing(fn ($state) => $state?->format('d/m/Y H:i:s')),

                                        TextInput::make('generated_conversions')
                                            ->label('Conversions générées')
                                            ->disabled()
                                            ->dehydrated(false),
                                        TextInput::make('responsive_images_count')
                                            ->label('Images responsives')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($state) => $state ?? 'Aucune'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
