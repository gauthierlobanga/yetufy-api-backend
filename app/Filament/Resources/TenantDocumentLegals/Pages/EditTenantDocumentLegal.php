<?php

namespace App\Filament\Resources\TenantDocumentLegals\Pages;

use App\Filament\Resources\TenantDocumentLegals\TenantDocumentLegalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTenantDocumentLegal extends EditRecord
{
    protected static string $resource = TenantDocumentLegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
