<?php

namespace App\Filament\Resources\TypeDocumentLegals\Pages;

use App\Filament\Resources\TypeDocumentLegals\TypeDocumentLegalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTypeDocumentLegal extends EditRecord
{
    protected static string $resource = TypeDocumentLegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
