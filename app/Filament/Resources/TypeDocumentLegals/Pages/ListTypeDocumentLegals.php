<?php

namespace App\Filament\Resources\TypeDocumentLegals\Pages;

use App\Filament\Resources\TypeDocumentLegals\TypeDocumentLegalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTypeDocumentLegals extends ListRecords
{
    protected static string $resource = TypeDocumentLegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
