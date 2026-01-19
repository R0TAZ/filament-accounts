<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use App\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Rotaz\FilamentAccounts\Contracts\InvitesAccountParties;
use Rotaz\FilamentAccounts\Events\InvitingAccountParty;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\Mail\AccountInvitation;
use Rotaz\FilamentAccounts\Rules\Role;

class InviteAccountParty implements InvitesAccountParties
{
    /**
     * Invite a new account party to the given account.
     *
     * @throws AuthorizationException
     */
    public function invite(User $user, Account $account, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addAccountParty', $account);

        $this->validate($account, $email, $role);

        InvitingAccountParty::dispatch($account, $email, $role);

        $invitation = $account->accountInvitations()->create([
            'email' => $email,
            'role' => $role,
        ]);
        $mailableClass = config('filament-accounts.account.invitations.invite_mail_template', AccountInvitation::class);

        Mail::to($email)->send(new $mailableClass($invitation));
    }

    /**
     * Validate the invite employee operation.
     */
    protected function validate(Account $account, string $email, ?string $role): void
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules($account), [
            'email.unique' => __('filament-accounts::default.errors.party_already_invited'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnAccount($account, $email)
        )->validateWithBag('addAccountParty');
    }

    /**
     * Get the validation rules for inviting a account party.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(Account $account): array
    {
        return array_filter([
            'email' => [
                'required', 'email',
                Rule::unique('account_invitations')->where(static function (Builder $query) use ($account) {
                    $query->where('account_id', $account->id);
                }),
            ],
            'role' => FilamentAccounts::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    /**
     * Ensure that the parrty is not already on the account.
     */
    protected function ensureUserIsNotAlreadyOnAccount(Account $account, string $email): Closure
    {
        return static function ($validator) use ($account, $email) {
            $validator->errors()->addIf(
                $account->hasUserWithEmail($email),
                'email',
                __('filament-accounts::default.errors.party_already_belongs_to_account')
            );
        };
    }
}
