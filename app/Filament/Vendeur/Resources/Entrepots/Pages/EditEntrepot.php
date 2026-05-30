<?php

namespace App\Filament\Vendeur\Resources\Entrepots\Pages;

use App\Filament\Vendeur\Resources\Entrepots\EntrepotResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEntrepot extends EditRecord
{
    protected static string $resource = EntrepotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
