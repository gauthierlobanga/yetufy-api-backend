<?php

namespace App\Filament\Vendeur\Resources\CampagneMarketings\Pages;

use App\Filament\Vendeur\Resources\CampagneMarketings\CampagneMarketingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampagneMarketings extends ListRecords
{
    protected static string $resource = CampagneMarketingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
