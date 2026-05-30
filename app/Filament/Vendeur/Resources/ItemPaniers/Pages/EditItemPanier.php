<?php

namespace App\Filament\Vendeur\Resources\ItemPaniers\Pages;

use App\Filament\Vendeur\Resources\ItemPaniers\ItemPanierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditItemPanier extends EditRecord
{
    protected static string $resource = ItemPanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
