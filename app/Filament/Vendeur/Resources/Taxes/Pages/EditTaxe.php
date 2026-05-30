<?php

namespace App\Filament\Vendeur\Resources\Taxes\Pages;

use App\Filament\Vendeur\Resources\Taxes\TaxeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxe extends EditRecord
{
    protected static string $resource = TaxeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
