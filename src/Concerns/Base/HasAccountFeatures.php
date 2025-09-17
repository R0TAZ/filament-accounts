<?php

namespace Rotaz\FilamentAccounts\Concerns\Base;

use Closure;
use Rotaz\FilamentAccounts\HasAccounts;

trait HasAccountFeatures
{
    /**
     * The event listener to register.
     */
    protected static bool $switchesCurrentAccount = false;

    /**
     * Determine if the Account is supporting account features.
     */
    public static bool $hasAccountFeatures = false;

    /**
     * Determine if invitations are sent to account employees.
     */
    public static bool $sendsAccountInvitations = false;

    /**
     * Determine if the application supports switching current account.
     */
    public function switchCurrentAccount(bool $condition = true): static
    {
        static::$switchesCurrentAccount = $condition;

        return $this;
    }

    /**
     * Determine if the account is supporting account features.
     */
    public function companies(bool | Closure | null $condition = true, bool $invitations = false): static
    {
        static::$hasAccountFeatures = $condition instanceof Closure ? $condition() : $condition;
        static::$sendsAccountInvitations = $invitations;

        return $this;
    }

    /**
     * Determine if the application switches the current account.
     */
    public static function switchesCurrentAccount(): bool
    {
        return static::$switchesCurrentAccount;
    }

    /**
     * Determine if Account is supporting account features.
     */
    public static function hasAccountFeatures(): bool
    {
        return static::$hasAccountFeatures;
    }

    /**
     * Determine if invitations are sent to account employees.
     */
    public static function sendsAccountInvitations(): bool
    {
        return static::hasAccountFeatures() && static::$sendsAccountInvitations;
    }

    /**
     * Determine if a given user model utilizes the "HasAccounts" trait.
     */
    public static function userHasAccountFeatures(mixed $user): bool
    {
        return (array_key_exists(HasAccounts::class, class_uses_recursive($user)) ||
                method_exists($user, 'currentAccount')) &&
            static::hasAccountFeatures();
    }
}
