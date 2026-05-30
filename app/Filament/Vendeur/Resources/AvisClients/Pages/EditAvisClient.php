<?php

namespace App\Filament\Vendeur\Resources\AvisClients\Pages;

use App\Filament\Vendeur\Resources\AvisClients\AvisClientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAvisClient extends EditRecord
{
    protected static string $resource = AvisClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
