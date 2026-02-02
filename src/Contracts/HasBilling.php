<?php

namespace Rotaz\FilamentAccounts\Contracts;

/**
 * @method modelClass(): string
 * @method modelKey(): mixed
 * @method subscribed(): bool
 * @method subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
 * @method subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
 * @method subscriptionWarnings(): ?array
 */
interface HasBilling {}
