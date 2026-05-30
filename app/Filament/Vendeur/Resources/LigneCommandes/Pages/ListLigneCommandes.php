<?php

namespace App\Filament\Vendeur\Resources\LigneCommandes\Pages;

use App\Filament\Vendeur\Resources\LigneCommandes\LigneCommandeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLigneCommandes extends ListRecords
{
    protected static string $resource = LigneCommandeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
