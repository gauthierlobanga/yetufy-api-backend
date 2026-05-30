<?php

namespace App\Filament\Vendeur\Resources\RelancePaniers\Pages;

use App\Filament\Vendeur\Resources\RelancePaniers\RelancePanierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRelancePanier extends EditRecord
{
    protected static string $resource = RelancePanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
