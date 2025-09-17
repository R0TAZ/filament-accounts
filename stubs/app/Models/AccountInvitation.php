<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rotaz\FilamentAccounts\FilamentAccounts;


class AccountInvitation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Get the account that owns the invitation.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(FilamentAccounts::accountModel());
    }
}
