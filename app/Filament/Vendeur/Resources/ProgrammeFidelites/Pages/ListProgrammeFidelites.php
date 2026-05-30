<?php

namespace App\Filament\Vendeur\Resources\ProgrammeFidelites\Pages;

use App\Filament\Vendeur\Resources\ProgrammeFidelites\ProgrammeFideliteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgrammeFidelites extends ListRecords
{
    protected static string $resource = ProgrammeFideliteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
