<?php

namespace App\Filament\Vendeur\Resources\LivraisonPaniers\Pages;

use App\Filament\Vendeur\Resources\LivraisonPaniers\LivraisonPanierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLivraisonPanier extends EditRecord
{
    protected static string $resource = LivraisonPanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
