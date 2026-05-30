<?php

namespace App\Filament\Resources\VendorRequests\Pages;

use App\Filament\Resources\VendorRequests\VendorRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVendorRequest extends EditRecord
{
    protected static string $resource = VendorRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
