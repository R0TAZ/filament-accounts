<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Rotaz\FilamentAccounts\Contracts\HasBilling;

abstract class Account extends Model implements HasBilling
{
    use CanBilling;
    public function owner(): BelongsTo
    {
        return $this->belongsTo(FilamentAccounts::userModel(), 'user_id');
    }

    public function allUsers(): Collection
    {
        return $this->users->merge([$this->owner]);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(FilamentAccounts::userModel(), FilamentAccounts::partyModel())
            ->withPivot('role')
            ->withTimestamps()
            ->as('party');
    }

    public function hasUser(mixed $user): bool
    {
        return $this->users->contains($user) || $user->ownsAccount($this);
    }

    public function hasUserWithEmail(string $email): bool
    {
        return $this->allUsers()->contains(static function ($user) use ($email) {
            return $user->email === $email;
        });
    }

    public function userHasPermission(mixed $user, string $permission): bool
    {
        return $user->hasAccountPermission($this, $permission);
    }

    public function accountInvitations(): HasMany
    {
        return $this->hasMany(FilamentAccounts::accountInvitationModel());
    }

    public function removeUser(mixed $user): void
    {
        if ($user->current_account_id === $this->id) {
            $user->forceFill([
                'current_account_id' => null,
            ])->save();
        }

        $this->users()->detach($user);
    }

    public function purge(): void
    {
        $this->owner()->where('current_account_id', $this->id)
            ->update(['current_account_id' => null]);

        $this->users()->where('current_account_id', $this->id)
            ->update(['current_account_id' => null]);

        $this->users()->detach();

        $this->delete();
    }
}