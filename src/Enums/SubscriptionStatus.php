<?php

namespace Rotaz\FilamentAccounts\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatus: string implements HasColor, HasIcon, HasLabel
{
    case TRIALING  = 'Trialing';
    case ACTIVE    = 'Active';
    case CANCELLED = 'Cancelled';
    case EXPIRED   = 'Expired';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TRIALING  => 'Trialing',
            self::ACTIVE    => 'Active',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED   => 'Expired',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::TRIALING  => 'info',
            self::ACTIVE    => 'success',
            self::CANCELLED => 'warning',
            self::EXPIRED   => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::TRIALING  => 'heroicon-m-clock',
            self::ACTIVE    => 'heroicon-m-check-circle',
            self::CANCELLED => 'heroicon-m-circle',
            self::EXPIRED   => 'heroicon-m-x-circle',
        };
    }
}
