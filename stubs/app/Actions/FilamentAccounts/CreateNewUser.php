<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Rotaz\FilamentAccounts\Contracts\CreatesNewUsers;
use Rotaz\FilamentAccounts\FilamentAccounts;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => FilamentAccounts::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]), function (User $user) {
                $this->createAccount($user);
            });
        });
    }

    /**
     * Create a personal account for the user.
     */
    protected function createAccount(User $user): void
    {
        $user->ownedAccounts()->save(Account::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . " ACCOUNT",
            'personal_account' => true,
        ]));
    }
}
