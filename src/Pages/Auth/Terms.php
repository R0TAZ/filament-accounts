<?php

namespace Rotaz\FilamentAccounts\Pages\Auth;

use Filament\Pages\Concerns\HasRoutes;
use Filament\Pages\SimplePage;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Rotaz\FilamentAccounts\FilamentAccounts;


class Terms extends SimplePage
{
    use HasRoutes;

    protected static string $view = 'filament-accounts::auth.terms';

    protected function getViewData(): array
    {
        $termsFile = FilamentAccounts::localizedMarkdownPath('terms.md');

        return [
            'terms' => Str::markdown(file_get_contents($termsFile)),
        ];
    }

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    public function getMaxWidth(): MaxWidth | string | null
    {
        return MaxWidth::TwoExtraLarge;
    }

    public static function getSlug(): string
    {
        return static::$slug ?? 'terms-of-service';
    }

    public static function getRouteName(): string
    {
        return 'auth.terms';
    }
}
