<?php

namespace App\Filament\Vendeur\Resources\CommandeAchats\Pages;

use App\Filament\Vendeur\Resources\CommandeAchats\CommandeAchatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommandeAchats extends ListRecords
{
    protected static string $resource = CommandeAchatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
