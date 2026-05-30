<?php

namespace App\Filament\Vendeur\Resources\Adresses\Pages;

use App\Filament\Vendeur\Resources\Adresses\AdresseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAdresse extends EditRecord
{
    protected static string $resource = AdresseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
