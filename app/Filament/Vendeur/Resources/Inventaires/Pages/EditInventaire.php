<?php

namespace App\Filament\Vendeur\Resources\Inventaires\Pages;

use App\Filament\Vendeur\Resources\Inventaires\InventaireResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditInventaire extends EditRecord
{
    protected static string $resource = InventaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
