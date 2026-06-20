<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

// use Filament\Support\Icons\Heroicon;
// use STS\FilamentImpersonate\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon('heroicon-m-eye'),

            DeleteAction::make(),

            // Impersonate::make()
            //     ->record($this->getRecord())
            //     ->label('Impersonner')
            //     ->icon(Heroicon::Swatch)
            //     ->color('warning')
            //     ->requiresConfirmation(),
        ];
    }
}
