<?php

namespace Rotaz\FilamentAccounts\Rules;

use Illuminate\Contracts\Validation\Rule;
use Rotaz\FilamentAccounts\FilamentAccounts;

class Role implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        return array_key_exists($value, FilamentAccounts::$roles);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return __('filament-accounts::default.errors.valid_role');
    }
}
