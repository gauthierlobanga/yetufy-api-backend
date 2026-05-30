<?php

namespace App\Filament\Vendeur\Resources\CampagneMarketings\Pages;

use App\Filament\Vendeur\Resources\CampagneMarketings\CampagneMarketingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCampagneMarketing extends EditRecord
{
    protected static string $resource = CampagneMarketingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
