<?php

namespace App\Filament\Vendeur\Resources\ReglePaniers\Pages;

use App\Filament\Vendeur\Resources\ReglePaniers\ReglePanierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReglePaniers extends ListRecords
{
    protected static string $resource = ReglePanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
