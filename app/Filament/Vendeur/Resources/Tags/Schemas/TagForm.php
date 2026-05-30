<?php

namespace App\Filament\Vendeur\Resources\Tags\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations du tag')
                    ->icon('heroicon-o-tag')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                // Nom du tag (support multi-langues)
                                TextInput::make('name.fr')
                                    ->label('Nom (Français)')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (empty($state)) {
                                            return;
                                        }
                                        $set('slug.fr', Str::slug($state));
                                    })
                                    ->placeholder('Ex: Nouveauté, Promotion'),

                                TextInput::make('name.en')
                                    ->label('Nom (English)')
                                    ->maxLength(255)
                                    ->placeholder('Ex: New, Promotion'),

                                TextInput::make('slug.fr')
                                    ->label('Slug (Français)')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Identifiant unique pour l\'URL'),

                                TextInput::make('slug.en')
                                    ->label('Slug (English)')
                                    ->maxLength(255),

                                ColorPicker::make('color')
                                    ->label('Couleur')
                                    ->helperText('Couleur d\'accentuation pour le tag')
                                    ->default('#6B7280'),

                                Select::make('type')
                                    ->label('Type de tag')
                                    ->options([
                                        '' => 'Général',
                                        'product' => 'Produit',
                                        'post' => 'Article',
                                        'category' => 'Catégorie',
                                    ])
                                    ->nullable()
                                    ->helperText('Catégorise les tags par usage'),

                                TextInput::make('order_column')
                                    ->label("Ordre d'affichage")
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('Plus le chiffre est petit, plus le tag apparaît en haut'),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Description optionnelle du tag')
                            ->helperText('Description du tag pour les moteurs de recherche'),

                        ToggleButtons::make('is_active')
                            ->label('Tag actif')
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
                    ]),
            ]);
    }
}
