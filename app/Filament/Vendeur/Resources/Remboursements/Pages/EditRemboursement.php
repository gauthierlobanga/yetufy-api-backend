<?php

namespace App\Filament\Vendeur\Resources\Remboursements\Pages;

use App\Filament\Vendeur\Resources\Remboursements\RemboursementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRemboursement extends EditRecord
{
    protected static string $resource = RemboursementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
