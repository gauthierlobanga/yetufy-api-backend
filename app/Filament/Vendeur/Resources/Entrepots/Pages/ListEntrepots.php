<?php

namespace App\Filament\Vendeur\Resources\Entrepots\Pages;

use App\Filament\Vendeur\Resources\Entrepots\EntrepotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntrepots extends ListRecords
{
    protected static string $resource = EntrepotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
