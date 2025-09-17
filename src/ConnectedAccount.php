<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Rotaz\FilamentAccounts\Credentials;

abstract class ConnectedAccount extends Model
{
    public function getCredentials(): Credentials
    {
        return new Credentials($this);
    }

    /**
     * Get user of the connected account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(FilamentAccounts::userModel(), 'user_id', FilamentAccounts::newUserModel()->getAuthIdentifierName());
    }

    /**
     * Get the data that should be shared.
     *
     * @return array<string, mixed>
     */
    public function getSharedData(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'avatar_path' => $this->avatar_path,
            'created_at' => $this->created_at?->diffForHumans(),
        ];
    }
}