<?php

namespace App\Filament\Vendeur\Resources\PromotionClients\Pages;

use App\Filament\Vendeur\Resources\PromotionClients\PromotionClientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPromotionClient extends EditRecord
{
    protected static string $resource = PromotionClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
