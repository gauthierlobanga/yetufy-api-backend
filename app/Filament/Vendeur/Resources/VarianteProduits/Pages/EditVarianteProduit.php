<?php

namespace App\Filament\Vendeur\Resources\VarianteProduits\Pages;

use App\Filament\Vendeur\Resources\VarianteProduits\VarianteProduitResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVarianteProduit extends EditRecord
{
    protected static string $resource = VarianteProduitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
