<?php

namespace App\Filament\Vendeur\Resources\AbandonPaniers\Pages;

use App\Filament\Vendeur\Resources\AbandonPaniers\AbandonPanierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAbandonPaniers extends ListRecords
{
    protected static string $resource = AbandonPanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
