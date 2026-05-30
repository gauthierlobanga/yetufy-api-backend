<?php

namespace App\Filament\Resources\TenantDocumentLegals\Pages;

use App\Filament\Resources\TenantDocumentLegals\TenantDocumentLegalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTenantDocumentLegals extends ListRecords
{
    protected static string $resource = TenantDocumentLegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
