<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

trait HasAccounts
{
    public function isCurrentAccount(mixed $account): bool
    {
        return $account->id === $this->currentAccount->id;
    }

    public function currentAccount(): BelongsTo
    {
        if ($this->current_account_id === null && $this->id) {
            $this->switchAccount($this->personalAccount());
        }

        return $this->belongsTo(FilamentAccounts::accountModel(), 'current_account_id');
    }

    public function switchAccount(mixed $account): bool
    {
        if (! $this->belongsToAccount($account)) {
            return false;
        }

        $this->forceFill([
            'current_account_id' => $account->id,
        ])->save();

        $this->setRelation('currentAccount', $account);

        return true;
    }

    public function allAccounts(): Collection
    {
        return $this->ownedAccounts->merge($this->accounts)->sortBy('name');
    }

    public function ownedAccounts(): HasMany
    {
        return $this->hasMany(FilamentAccounts::accountModel());
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(FilamentAccounts::accountModel(), FilamentAccounts::partyModel())
            ->withPivot('role')
            ->withTimestamps()
            ->as('party');
    }

    public function personalAccount(): mixed
    {
        return $this->ownedAccounts->where('personal_account', true)->first();
    }

    public function primaryAccount(): mixed
    {
        return $this->personalAccount() ?? $this->allAccounts()->first();
    }

    public function hasAnyAccounts(): bool
    {
        return $this->allAccounts()->isNotEmpty();
    }

    public function ownsAccount(mixed $account): bool
    {
        if ($account === null) {
            return false;
        }

        return $this->id === $account->{$this->getForeignKey()};
    }

    public function belongsToAccount(mixed $account): bool
    {
        if ($account === null) {
            return false;
        }

        return $this->ownsAccount($account) || $this->accounts->contains(static function ($t) use ($account) {
                return $t->id === $account->id;
            });
    }
    public function accountRole(mixed $account): ?Role
    {
        if ($this->ownsAccount($account)) {
            return new OwnerRole;
        }

        if (! $this->belongsToAccount($account)) {
            return null;
        }

        $role = $account->users
            ->where('id', $this->id)
            ->first()
            ->party
            ->role;

        return $role ? FilamentAccounts::findRole($role) : null;
    }

    public function hasAccountRole(mixed $account, string $role): bool
    {
        if ($this->ownsAccount($account)) {
            return true;
        }

        return $this->belongsToAccount($account) && FilamentAccounts::findRole($account->users->where(
                'id',
                $this->id
            )->first()->party->role)?->key === $role;
    }

    public function accountPermissions(mixed $account): array
    {
        if ($this->ownsAccount($account)) {
            return ['*'];
        }

        if (! $this->belongsToAccount($account)) {
            return [];
        }

        return (array) $this->accountRole($account)?->permissions;
    }

    public function hasAccountPermission(mixed $account, string $permission): bool
    {
        if ($this->ownsAccount($account)) {
            return true;
        }

        if (! $this->belongsToAccount($account)) {
            return false;
        }

        if ($this->currentAccessToken() !== null &&
            ! $this->tokenCan($permission) &&
            in_array(HasApiTokens::class, class_uses_recursive($this), true)) {
            return false;
        }

        $permissions = $this->accountPermissions($account);

        return in_array($permission, $permissions, true) ||
            in_array('*', $permissions, true) ||
            (Str::endsWith($permission, ':create') && in_array('*:create', $permissions, true)) ||
            (Str::endsWith($permission, ':update') && in_array('*:update', $permissions, true));
    }
}