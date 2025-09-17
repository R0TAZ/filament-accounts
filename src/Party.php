<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class Party extends Pivot
{
    protected $table = 'account_user';
}