<?php

namespace App\Filament\Vendeur\Resources\Commandes\Pages;

use App\Filament\Vendeur\Resources\Commandes\CommandeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommandes extends ListRecords
{
    protected static string $resource = CommandeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
