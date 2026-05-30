<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum AddressType: string implements HasColor, HasDescription, HasLabel
{
    case TYPE_FACTURATION = 'facturation';

    case TYPE_LIVRAISON = 'livraison';

    case TYPE_PRINCIPALE = 'principale';

    case TYPE_SECONDAIRE = 'secondaire';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::TYPE_FACTURATION => 'Facturation',
            self::TYPE_LIVRAISON => 'Livraison',
            self::TYPE_PRINCIPALE => 'principale',
            self::TYPE_SECONDAIRE => 'secondaire',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TYPE_FACTURATION => 'primary',
            self::TYPE_LIVRAISON => 'success',
            self::TYPE_PRINCIPALE => 'indigo',
            self::TYPE_SECONDAIRE => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::TYPE_FACTURATION => Heroicon::DocumentText,
            self::TYPE_LIVRAISON => Heroicon::Truck,
            self::TYPE_PRINCIPALE => Heroicon::Truck,
            self::TYPE_SECONDAIRE => Heroicon::Truck,
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::TYPE_FACTURATION => 'Adresse utilisée pour les factures et documents légaux',
            self::TYPE_LIVRAISON => 'Adresse utilisée pour la livraison des commandes',
            self::TYPE_PRINCIPALE => 'Adresse principale',
            self::TYPE_SECONDAIRE => 'Adresse secondaire',
        };
    }
}
