<?php

namespace App\Filament\Vendeur\Resources\TransactionFidelites\Pages;

use App\Filament\Vendeur\Resources\TransactionFidelites\TransactionFideliteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransactionFidelite extends EditRecord
{
    protected static string $resource = TransactionFideliteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
