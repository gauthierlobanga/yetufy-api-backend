<?php

namespace App\Filament\Vendeur\Resources\LigneCommandeAchats\Pages;

use App\Filament\Vendeur\Resources\LigneCommandeAchats\LigneCommandeAchatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLigneCommandeAchats extends ListRecords
{
    protected static string $resource = LigneCommandeAchatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
