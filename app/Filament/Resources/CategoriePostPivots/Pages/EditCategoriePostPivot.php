<?php

namespace App\Filament\Resources\CategoriePostPivots\Pages;

use App\Filament\Resources\CategoriePostPivots\CategoriePostPivotResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoriePostPivot extends EditRecord
{
    protected static string $resource = CategoriePostPivotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
