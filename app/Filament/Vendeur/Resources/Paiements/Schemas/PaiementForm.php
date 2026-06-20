<?php

namespace App\Filament\Vendeur\Resources\Paiements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaiementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('commande_id')
                    ->relationship('commande', 'id')
                    ->required(),
                TextInput::make('reference')
                    ->required(),
                TextInput::make('transaction_id'),
                TextInput::make('mode')
                    ->required(),
                TextInput::make('carte_brand'),
                TextInput::make('carte_last4'),
                TextInput::make('montant')
                    ->required()
                    ->numeric(),
                TextInput::make('devise')
                    ->required()
                    ->default('EUR'),
                TextInput::make('statut')
                    ->required()
                    ->default('en_attente'),
                TextInput::make('details'),
                DateTimePicker::make('date_paiement'),
                DateTimePicker::make('date_remboursement'),
            ]);
    }
}
