<?php

namespace App\Filament\Vendeur\Resources\ProduitFournisseurs\Pages;

use App\Filament\Vendeur\Resources\ProduitFournisseurs\ProduitFournisseurResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduitFournisseur extends EditRecord
{
    protected static string $resource = ProduitFournisseurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
