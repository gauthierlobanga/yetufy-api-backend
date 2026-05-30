<?php

namespace App\Filament\Vendeur\Resources\Devises\Pages;

use App\Filament\Vendeur\Resources\Devises\DeviseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDevises extends ListRecords
{
    protected static string $resource = DeviseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
