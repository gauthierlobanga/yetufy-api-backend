<?php

namespace App\Filament\Vendeur\Resources\LigneCommandes\Pages;

use App\Filament\Vendeur\Resources\LigneCommandes\LigneCommandeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLigneCommande extends EditRecord
{
    protected static string $resource = LigneCommandeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
