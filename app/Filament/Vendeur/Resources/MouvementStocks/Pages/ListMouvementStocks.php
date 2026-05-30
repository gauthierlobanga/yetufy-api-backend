<?php

namespace App\Filament\Vendeur\Resources\MouvementStocks\Pages;

use App\Filament\Vendeur\Resources\MouvementStocks\MouvementStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMouvementStocks extends ListRecords
{
    protected static string $resource = MouvementStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
