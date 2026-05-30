<?php

namespace App\Filament\Vendeur\Resources\MouvementStocks\Pages;

use App\Filament\Vendeur\Resources\MouvementStocks\MouvementStockResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMouvementStock extends EditRecord
{
    protected static string $resource = MouvementStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
