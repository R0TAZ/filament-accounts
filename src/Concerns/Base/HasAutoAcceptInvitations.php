<?php

namespace Rotaz\FilamentAccounts\Concerns\Base;

trait HasAutoAcceptInvitations
{
    /**
     * Determines whether invited users are exempt from creating a personal account after registration.
     */
    public static bool $autoAcceptInvitations = false;

    /**
     * Configure the system to allow invited users to register without creating a personal account.
     */
    public function autoAcceptInvitations(): static
    {
        static::$autoAcceptInvitations = true;

        return $this;
    }

    /**
     * Check if invited users can register without creating a personal account.
     */
    public static function autoAcceptsInvitations(): string
    {
        return static::$autoAcceptInvitations;
    }
}
