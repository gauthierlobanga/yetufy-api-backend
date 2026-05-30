<?php

namespace App\Filament\Vendeur\Resources\LivraisonPaniers\Pages;

use App\Filament\Vendeur\Resources\LivraisonPaniers\LivraisonPanierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLivraisonPaniers extends ListRecords
{
    protected static string $resource = LivraisonPanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
