<?php

namespace App\Filament\Vendeur\Resources\WishlistItems\Pages;

use App\Filament\Vendeur\Resources\WishlistItems\WishlistItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditWishlistItem extends EditRecord
{
    protected static string $resource = WishlistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
