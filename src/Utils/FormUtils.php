<?php

namespace Rotaz\FilamentAccounts\Utils;

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

}
