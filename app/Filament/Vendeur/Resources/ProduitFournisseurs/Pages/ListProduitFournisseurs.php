<?php

namespace App\Filament\Vendeur\Resources\ProduitFournisseurs\Pages;

use App\Filament\Vendeur\Resources\ProduitFournisseurs\ProduitFournisseurResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProduitFournisseurs extends ListRecords
{
    protected static string $resource = ProduitFournisseurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
