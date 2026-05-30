<?php

namespace App\Filament\Vendeur\Resources\ReglePaniers\Pages;

use App\Filament\Vendeur\Resources\ReglePaniers\ReglePanierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditReglePanier extends EditRecord
{
    protected static string $resource = ReglePanierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
