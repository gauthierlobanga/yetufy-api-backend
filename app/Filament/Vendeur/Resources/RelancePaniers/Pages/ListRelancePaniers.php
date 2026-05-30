<?php

namespace App\Filament\Vendeur\Resources\RelancePaniers\Pages;

use App\Filament\Vendeur\Resources\RelancePaniers\RelancePanierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRelancePaniers extends ListRecords
{
    protected static string $resource = RelancePanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
