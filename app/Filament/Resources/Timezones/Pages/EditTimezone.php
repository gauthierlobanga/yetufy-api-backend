<?php

namespace App\Filament\Resources\Timezones\Pages;

use App\Filament\Resources\Timezones\TimezoneResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimezone extends EditRecord
{
    protected static string $resource = TimezoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
