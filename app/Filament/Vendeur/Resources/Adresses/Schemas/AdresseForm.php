<?php

namespace App\Filament\Vendeur\Resources\Adresses\Schemas;

use App\Enums\AddressType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdresseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Adresse')
                    ->icon('heroicon-o-map-pin')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                TextInput::make('rue')
                                    ->label('Rue')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('1 rue de la Paix')
                                    ->columnSpanFull(),

                                TextInput::make('complement')
                                    ->label('Complément')
                                    ->maxLength(255)
                                    ->placeholder('Appartement 12, Bâtiment B')
                                    ->columnSpanFull(),

                                TextInput::make('code_postal')
                                    ->label('Code postal')
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('75001'),

                                TextInput::make('ville')
                                    ->label('Ville')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Paris'),

                                TextInput::make('pays')
                                    ->label('Pays')
                                    ->required()
                                    ->maxLength(255)
                                    ->default('France')
                                    ->placeholder('France'),

                                TextInput::make('region')
                                    ->label('Région / Département')
                                    ->maxLength(255)
                                    ->placeholder('Île-de-France'),
                            ]),
                    ]),

                Section::make('Coordonnées')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('telephone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+33 1 23 45 67 89')
                            ->prefixIcon('heroicon-m-phone'),

                        Textarea::make('instructions')
                            ->label('Instructions de livraison')
                            ->rows(2)
                            ->placeholder('Code d\'accès, étage, sonnette...')
                            ->helperText('Informations supplémentaires pour le livreur'),
                    ]),

                Section::make('Configuration')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Type d\'adresse')
                                    ->options(AddressType::class)
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->default(AddressType::TYPE_LIVRAISON)
                                    ->helperText('Facturation = adresse pour les factures, Livraison = adresse pour la livraison'),

                                Toggle::make('est_defaut')
                                    ->label('Adresse par défaut')
                                    ->default(false)
                                    ->helperText('Cette adresse sera utilisée par défaut pour ce type'),
                            ]),
                    ]),

                // Section cachée pour la relation polymorphique
                Group::make()
                    ->schema([
                        TextInput::make('addressable_type')
                            ->hidden()
                            ->default('App\Models\Client'),

                        TextInput::make('addressable_id')
                            ->hidden(),
                    ]),
            ]);
    }
}
