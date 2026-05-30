<?php

namespace App\Filament\Vendeur\Resources\WishlistItems\Pages;

use App\Filament\Vendeur\Resources\WishlistItems\WishlistItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWishlistItem extends CreateRecord
{
    protected static string $resource = WishlistItemResource::class;
}
