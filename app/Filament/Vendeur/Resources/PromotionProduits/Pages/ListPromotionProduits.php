<?php

namespace App\Filament\Vendeur\Resources\PromotionProduits\Pages;

use App\Filament\Vendeur\Resources\PromotionProduits\PromotionProduitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromotionProduits extends ListRecords
{
    protected static string $resource = PromotionProduitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
