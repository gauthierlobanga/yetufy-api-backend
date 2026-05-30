<?php

namespace App\Filament\Vendeur\Resources\PromotionClients\Pages;

use App\Filament\Vendeur\Resources\PromotionClients\PromotionClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromotionClients extends ListRecords
{
    protected static string $resource = PromotionClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
