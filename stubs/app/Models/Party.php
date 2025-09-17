<?php

namespace App\Models;

use Rotaz\FilamentAccounts\Party as FilamentCompaniesParty;

class Party extends FilamentCompaniesParty
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
