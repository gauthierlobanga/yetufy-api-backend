<?php

namespace App\Filament\Vendeur\Resources\Retours\Pages;

use App\Filament\Vendeur\Resources\Retours\RetourResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRetour extends EditRecord
{
    protected static string $resource = RetourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
