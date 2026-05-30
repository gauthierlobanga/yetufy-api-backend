<?php

namespace App\Filament\Vendeur\Resources\PromotionPaniers\Pages;

use App\Filament\Vendeur\Resources\PromotionPaniers\PromotionPanierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPromotionPanier extends EditRecord
{
    protected static string $resource = PromotionPanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
