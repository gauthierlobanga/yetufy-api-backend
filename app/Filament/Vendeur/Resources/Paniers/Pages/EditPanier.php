<?php

namespace App\Filament\Vendeur\Resources\Paniers\Pages;

use App\Filament\Vendeur\Resources\Paniers\PanierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPanier extends EditRecord
{
    protected static string $resource = PanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
