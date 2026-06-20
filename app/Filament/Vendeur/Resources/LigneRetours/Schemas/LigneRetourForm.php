<?php

namespace App\Filament\Vendeur\Resources\LigneRetours\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LigneRetourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('retour_id')
                    ->relationship('retour', 'id')
                    ->required(),
                Select::make('ligne_commande_id')
                    ->relationship('ligneCommande', 'id')
                    ->required(),
                TextInput::make('quantite')
                    ->required()
                    ->numeric(),
                TextInput::make('montant')
                    ->required()
                    ->numeric(),
                TextInput::make('etat')
                    ->required()
                    ->default('conforme'),
                Textarea::make('commentaire')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
            ]);
    }
}
