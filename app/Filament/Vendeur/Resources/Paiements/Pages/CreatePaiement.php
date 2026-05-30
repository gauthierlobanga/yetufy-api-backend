<?php

namespace App\Filament\Vendeur\Resources\Paiements\Pages;

use App\Filament\Vendeur\Resources\Paiements\PaiementResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaiement extends CreateRecord
{
    protected static string $resource = PaiementResource::class;
}
