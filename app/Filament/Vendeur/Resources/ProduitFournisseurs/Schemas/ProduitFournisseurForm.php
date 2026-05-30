<?php

namespace App\Filament\Vendeur\Resources\ProduitFournisseurs\Schemas;

use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProduitFournisseurForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }
}
