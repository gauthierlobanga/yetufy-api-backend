<?php

namespace App\Filament\Vendeur\Resources\ProgrammeFidelites\Pages;

use App\Filament\Vendeur\Resources\ProgrammeFidelites\ProgrammeFideliteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProgrammeFidelite extends EditRecord
{
    protected static string $resource = ProgrammeFideliteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
