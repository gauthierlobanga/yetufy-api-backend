<?php

namespace App\Filament\Resources\Vendeurs\Pages;

use App\Filament\Resources\Vendeurs\VendeurResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVendeurs extends ListRecords
{
    protected static string $resource = VendeurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
