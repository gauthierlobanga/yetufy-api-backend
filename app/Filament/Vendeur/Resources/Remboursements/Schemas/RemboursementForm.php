<?php

namespace App\Filament\Vendeur\Resources\Remboursements\Schemas;

use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RemboursementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('paiement_id')
                    ->relationship('paiement', 'id')
                    ->required(),
                Select::make('retour_id')
                    ->relationship('retour', 'id'),
                TextInput::make('reference')
                    ->required(),
                TextInput::make('montant')
                    ->required()
                    ->numeric(),
                TextInput::make('mode')
                    ->required(),
                TextInput::make('statut')
                    ->required()
                    ->default('en_attente'),
                TextInput::make('motif'),
                TextInput::make('details'),
                DateTimePicker::make('date_remboursement'),
            ]);
    }
}
