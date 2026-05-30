<?php

namespace App\Filament\Vendeur\Resources\Taxes\Pages;

use App\Filament\Vendeur\Resources\Taxes\TaxeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxes extends ListRecords
{
    protected static string $resource = TaxeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
