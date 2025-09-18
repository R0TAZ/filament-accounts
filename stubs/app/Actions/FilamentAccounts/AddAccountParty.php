<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use App\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Rotaz\FilamentAccounts\Contracts\AddsAccountParties;
use Rotaz\FilamentAccounts\Events\AddingAccountParty;
use Rotaz\FilamentAccounts\Events\AccountPartyAdded;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\Rules\Role;

class AddAccountParty implements AddsAccountParties
{
    /**
     * @throws AuthorizationException
     */
    public function add(User $user, Account $account, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addAccountParty', $account);

        $this->validate($account, $email, $role);

        $newAccountParty = FilamentAccounts::findUserByEmailOrFail($email);

        AddingAccountParty::dispatch($account, $newAccountParty);

        $account->users()->attach(
            $newAccountParty,
            ['role' => $role]
        );

        AccountPartyAdded::dispatch($account, $newAccountParty);
    }


    /**
     * @throws ValidationException
     */
    protected function validate(Account $account, string $email, ?string $role): void
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules(), [
            'email.exists' => __('filament-accounts::default.errors.email_not_found'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnAccount($account, $email)
        )->validateWithBag('addAccountParty');
    }

    /**
     * @return array<string, Rule|array|string>
     */
    protected function rules(): array
    {
        return array_filter([
            'email' => ['required', 'email', 'exists:users'],
            'role' => FilamentAccounts::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    protected function ensureUserIsNotAlreadyOnAccount(Account $account, string $email): Closure
    {
        return static function ($validator) use ($account, $email) {
            $validator->errors()->addIf(
                $account->hasUserWithEmail($email),
                'email',
                __('filament-accounts::default.errors.user_belongs_to_account')
            );
        };
    }
}
