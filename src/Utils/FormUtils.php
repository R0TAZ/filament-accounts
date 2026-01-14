<?php

namespace Rotaz\FilamentAccounts\Utils;

use Illuminate\Support\Str;

class FormUtils
{
    public static function getTextFormUpper(?array $currentSchema = []): array
    {
        return array_merge($currentSchema, [
            'autocomplete' => 'none',
            'style' => 'text-transform:uppercase',
        ]);
    }

    public static function getTextFormAutoCompleteOff(?array $currentSchema = []): array
    {
        return array_merge($currentSchema, [
            'autocomplete' => 'none',
        ]);
    }
    public static function only_numbers($state): string|null
    {
        if ( empty($state) ){
            return null;
        }

        $result = Str::of($state)->replaceMatches('/[^0-9]/', '');
        return Str::length($result) > 0 ? $result : null;
    }
}
