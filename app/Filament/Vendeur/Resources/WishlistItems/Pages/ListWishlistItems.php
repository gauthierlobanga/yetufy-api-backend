<?php

namespace App\Filament\Vendeur\Resources\WishlistItems\Pages;

use App\Filament\Vendeur\Resources\WishlistItems\WishlistItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWishlistItems extends ListRecords
{
    protected static string $resource = WishlistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
