<?php

namespace App\Filament\Vendeur\Resources\Paiements\Pages;

use App\Filament\Vendeur\Resources\Paiements\PaiementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaiements extends ListRecords
{
    protected static string $resource = PaiementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
