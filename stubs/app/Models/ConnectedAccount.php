<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Rotaz\FilamentAccounts\ConnectedAccount as SocialiteConnectedAccount;
use Rotaz\FilamentAccounts\Events\ConnectedAccountCreated;
use Rotaz\FilamentAccounts\Events\ConnectedAccountDeleted;
use Rotaz\FilamentAccounts\Events\ConnectedAccountUpdated;

class ConnectedAccount extends SocialiteConnectedAccount
{
    use HasTimestamps;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider',
        'provider_id',
        'name',
        'nickname',
        'email',
        'avatar_path',
        'token',
        'refresh_token',
        'expires_at',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => ConnectedAccountCreated::class,
        'updated' => ConnectedAccountUpdated::class,
        'deleted' => ConnectedAccountDeleted::class,
    ];
}
