<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum Status: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case ON_HOLD = 'on_hold';
    case CANCELLED = 'cancelled';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminé',
            self::ON_HOLD => 'En pause',
            self::CANCELLED => 'Annulé',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'warning',
            self::COMPLETED => 'success',
            self::ON_HOLD => 'info',
            self::CANCELLED => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PENDING => Heroicon::Clock,
            self::IN_PROGRESS => Heroicon::ArrowPath,
            self::COMPLETED => Heroicon::CheckCircle,
            self::ON_HOLD => Heroicon::PauseCircle,
            self::CANCELLED => Heroicon::XCircle,
        };
    }
}
