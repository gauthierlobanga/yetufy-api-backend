<?php

namespace App\Filament\Resources\CategoriePostPivots\Pages;

use App\Filament\Resources\CategoriePostPivots\CategoriePostPivotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoriePostPivots extends ListRecords
{
    protected static string $resource = CategoriePostPivotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
