<?php

namespace App\Filament\Vendeur\Resources\Wishlists\Pages;

use App\Filament\Vendeur\Resources\Wishlists\WishlistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWishlists extends ListRecords
{
    protected static string $resource = WishlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
