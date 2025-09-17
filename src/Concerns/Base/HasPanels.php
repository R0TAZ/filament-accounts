<?php

namespace Rotaz\FilamentAccounts\Concerns\Base;

use LogicException;

trait HasPanels
{
    /**
     * The user panel.
     */
    protected static string $userPanel;

    /**
     * The account panel.
     */
    protected static string $accountPanel;

    /**
     * Set the user panel.
     */
    public function userPanel(string $panel): static
    {
        static::$userPanel = $panel;

        return $this;
    }

    /**
     * Get the user panel configuration.
     */
    public static function getUserPanel(): string
    {
        if (! isset(static::$userPanel)) {
            throw new LogicException('FilamentAccounts plugin has not been configured with a user panel.');
        }

        return static::$userPanel;
    }

    /**
     * Determine if the user panel is set.
     */
    public static function hasUserPanel(): bool
    {
        return isset(static::$userPanel);
    }

    /**
     * Get the panel where the plugin is registered (The account panel).
     */
    public static function getAccountPanel(): string
    {
        if (! isset(static::$accountPanel)) {
            throw new LogicException('FilamentAccounts plugin has not been registered to any panel.');
        }

        return static::$accountPanel;
    }
}
