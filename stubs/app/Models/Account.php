<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Rotaz\FilamentAccounts\Account as FilamentAccountsAccount;
use Rotaz\FilamentAccounts\Events\AccountCreated;
use Rotaz\FilamentAccounts\Events\AccountDeleted;
use Rotaz\FilamentAccounts\Events\AccountUpdated;

class Account extends FilamentAccountsAccount implements HasAvatar
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_account',
        'slug',
        'contact_name'
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => AccountCreated::class,
        'updated' => AccountUpdated::class,
        'deleted' => AccountDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_account' => 'boolean',
        ];
    }

    public function getFilamentAvatarUrl(): string
    {
        return $this->owner->profile_photo_url;
    }
}
