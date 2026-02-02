<?php

namespace Rotaz\FilamentAccounts\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionInvoiceStatus: string implements HasColor, HasIcon, HasLabel
{
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case PENDING = 'pending';
    case CREATED = 'created';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
            self::PENDING => 'Pending',
            self::CREATED => 'Created',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PAID => 'success',
            self::CANCELLED => 'warning',
            self::PENDING , self::CREATED => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PAID => 'heroicon-m-check-circle',
            self::CANCELLED => 'heroicon-m-circle',
            self::PENDING => 'heroicon-m-pause-circle',
            self::CREATED => 'heroicon-m-plus-circle',
        };
    }
}
