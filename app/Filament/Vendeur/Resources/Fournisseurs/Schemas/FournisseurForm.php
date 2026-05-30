<?php

namespace App\Filament\Vendeur\Resources\Fournisseurs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FournisseurForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->required(),
                TextInput::make('contact'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('telephone')
                    ->tel(),
                Textarea::make('adresse')
                    ->columnSpanFull(),
                TextInput::make('siret'),
                TextInput::make('code_tva'),
                TextInput::make('conditions'),
                TextInput::make('coordonnees_bancaires'),
                Toggle::make('est_actif')
                    ->required(),
                TextInput::make('delai_livraison_jours')
                    ->required()
                    ->numeric()
                    ->default(7),
                TextInput::make('frais_port_min')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
            ]);
    }
}
