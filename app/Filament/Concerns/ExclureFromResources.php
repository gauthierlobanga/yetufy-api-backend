<?php

namespace App\Filament\Concerns;

trait ExclureFromResources
{
    public static function canAccess(): bool
    {
        if (filament()->getCurrentPanel()?->getId() === 'vendeur') {
            return false;
        }

        return parent::canAccess();
    }
}
