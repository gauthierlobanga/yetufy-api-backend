<?php

namespace App\Filament\Vendeur\Resources\Fournisseurs\Pages;

use App\Filament\Vendeur\Resources\Fournisseurs\FournisseurResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFournisseur extends EditRecord
{
    protected static string $resource = FournisseurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
