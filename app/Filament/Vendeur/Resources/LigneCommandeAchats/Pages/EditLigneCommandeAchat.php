<?php

namespace App\Filament\Vendeur\Resources\LigneCommandeAchats\Pages;

use App\Filament\Vendeur\Resources\LigneCommandeAchats\LigneCommandeAchatResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLigneCommandeAchat extends EditRecord
{
    protected static string $resource = LigneCommandeAchatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
