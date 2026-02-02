<?php

namespace Rotaz\FilamentAccounts\Enums;

use Filament\Support\Contracts\HasLabel;

enum SubscriptionCycle: string implements HasLabel
{
    case YEAR = 'year';
    case MONTH = 'month';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::YEAR => 'Yearly',
            self::MONTH => 'Monthly',
        };
    }

    public function getFieldPrefix(string $target = '_price'): ?string
    {
        return match ($this) {
            self::YEAR => 'yearly' . $target,
            self::MONTH => 'monthly' . $target,
        };
    }
}
