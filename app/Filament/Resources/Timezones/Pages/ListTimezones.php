<?php

namespace App\Filament\Resources\Timezones\Pages;

use App\Filament\Resources\Timezones\TimezoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimezones extends ListRecords
{
    protected static string $resource = TimezoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
