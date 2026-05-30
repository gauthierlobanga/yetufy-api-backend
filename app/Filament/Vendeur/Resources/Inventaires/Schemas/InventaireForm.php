<?php

namespace App\Filament\Vendeur\Resources\Inventaires\Schemas;

use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InventaireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('entrepot_id')
                    ->relationship('entrepot', 'id')
                    ->required(),
                TextInput::make('reference')
                    ->required(),
                TextInput::make('statut')
                    ->required()
                    ->default('en_cours'),
                TextInput::make('resultats'),
                DateTimePicker::make('date_debut'),
                DateTimePicker::make('date_fin'),
            ]);
    }
}
