<?php

namespace App\Filament\Vendeur\Resources\Promotions\Pages;

use App\Filament\Vendeur\Resources\Promotions\PromotionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromotions extends ListRecords
{
    protected static string $resource = PromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
