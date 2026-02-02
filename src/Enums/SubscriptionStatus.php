<?php

namespace Rotaz\FilamentAccounts\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatus: string implements HasColor, HasIcon, HasLabel
{
    case ACTIVE = 'Active';
    case CANCELLED = 'Cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::CANCELLED => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-m-check-circle',
            self::CANCELLED => 'heroicon-m-circle',
        };
    }
}
