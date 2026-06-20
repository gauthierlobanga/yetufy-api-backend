<?php

namespace App\Filament\Vendeur\Resources\LigneCommandeAchats\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LigneCommandeAchatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('commande_achat')
                    ->relationship('commandeAchat', 'id')
                    ->required(),
                Select::make('produit_id')
                    ->relationship('produit', 'id')
                    ->required(),
                TextInput::make('quantite')
                    ->required()
                    ->numeric(),
                TextInput::make('quantite_recue')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('prix_unitaire_ht')
                    ->required()
                    ->numeric(),
                TextInput::make('total_ht')
                    ->required()
                    ->numeric(),
                TextInput::make('tva')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('metadata'),
            ]);
    }
}
