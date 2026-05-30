<?php

namespace App\Filament\Resources\States\Pages;

use App\Filament\Resources\States\StateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditState extends EditRecord
{
    protected static string $resource = StateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
