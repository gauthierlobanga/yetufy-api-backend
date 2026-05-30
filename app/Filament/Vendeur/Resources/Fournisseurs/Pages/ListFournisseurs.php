<?php

namespace App\Filament\Vendeur\Resources\Fournisseurs\Pages;

use App\Filament\Vendeur\Resources\Fournisseurs\FournisseurResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFournisseurs extends ListRecords
{
    protected static string $resource = FournisseurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
