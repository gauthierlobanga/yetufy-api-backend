<?php

namespace App\Filament\Vendeur\Resources\Users\Pages;

use App\Filament\Vendeur\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use STS\FilamentImpersonate\Actions\Impersonate;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon('heroicon-m-pencil-square'),

            Impersonate::make()
                ->record($this->getRecord())
                ->label('Impersonner')
                ->icon(Heroicon::Swatch)
                ->color('warning')
                ->requiresConfirmation(),
        ];
    }
}
