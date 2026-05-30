<?php

namespace App\Filament\Vendeur\Resources\AbandonPaniers\Pages;

use App\Filament\Vendeur\Resources\AbandonPaniers\AbandonPanierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAbandonPanier extends EditRecord
{
    protected static string $resource = AbandonPanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
