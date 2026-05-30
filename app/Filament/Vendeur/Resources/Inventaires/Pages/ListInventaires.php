<?php

namespace App\Filament\Vendeur\Resources\Inventaires\Pages;

use App\Filament\Vendeur\Resources\Inventaires\InventaireResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventaires extends ListRecords
{
    protected static string $resource = InventaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
