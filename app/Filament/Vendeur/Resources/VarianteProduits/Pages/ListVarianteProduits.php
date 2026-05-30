<?php

namespace App\Filament\Vendeur\Resources\VarianteProduits\Pages;

use App\Filament\Vendeur\Resources\VarianteProduits\VarianteProduitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVarianteProduits extends ListRecords
{
    protected static string $resource = VarianteProduitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
