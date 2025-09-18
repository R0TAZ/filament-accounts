<?php

namespace App\Models;

use Rotaz\FilamentAccounts\Party as FilamentAccountParty;

class Party extends FilamentAccountParty
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
