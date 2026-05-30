<?php

namespace App\Filament\Vendeur\Resources\Adresses\Pages;

use App\Filament\Vendeur\Resources\Adresses\AdresseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdresses extends ListRecords
{
    protected static string $resource = AdresseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
