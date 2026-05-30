<?php

namespace App\Filament\Vendeur\Resources\PromotionProduits\Pages;

use App\Filament\Vendeur\Resources\PromotionProduits\PromotionProduitResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPromotionProduit extends EditRecord
{
    protected static string $resource = PromotionProduitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
