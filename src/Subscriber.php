<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Model;


abstract class Subscriber extends Model
{
    use CanBilling;

}
