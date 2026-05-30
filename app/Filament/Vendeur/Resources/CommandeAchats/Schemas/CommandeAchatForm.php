<?php

namespace App\Filament\Vendeur\Resources\CommandeAchats\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CommandeAchatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('fournisseur_id')
                    ->relationship('fournisseur', 'id')
                    ->required(),
                TextInput::make('numero_commande')
                    ->required(),
                DatePicker::make('date_commande')
                    ->required(),
                DatePicker::make('date_livraison_prevue'),
                DatePicker::make('date_livraison_reelle'),
                TextInput::make('statut')
                    ->required()
                    ->default('brouillon'),
                TextInput::make('sous_total_ht')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('remise')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('frais_livraison')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('taxe')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_ht')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_ttc')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
            ]);
    }
}
