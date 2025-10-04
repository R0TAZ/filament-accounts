<?php

namespace Rotaz\FilamentAccounts\Pages\Account;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rotaz\FilamentAccounts\Events\AddingAccount;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Filament\Pages\Auth\Register as FilamentRegister;
class CreateAccount extends FilamentRegister
{


    protected function handleRegistration(array $data): Model
    {
        $user = Auth::user();

        Gate::forUser($user)->authorize('create', FilamentAccounts::newAccountModel());

        AddingAccount::dispatch($user);

        $personalAccount = $user?->personalAccount() === null;

        $account = $user?->ownedAccounts()->create([
            'name' => $data['name'],
            'personal_account' => $personalAccount,
        ]);

        $user?->switchAccount($account);

        $name = $data['name'];

        $this->accountCreated($name);

        return $account;
    }

    protected function getUserModel(): string
    {
        if (isset($this->userModel)) {
            return $this->userModel;
        }

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        /** @var EloquentUserProvider $provider */
        $provider = $authGuard->getProvider();

        return $this->userModel = $provider->getModel();
    }

    protected function accountCreated($name): void
    {
        Notification::make()
            ->title(__('filament-accounts::default.notifications.account_created.title'))
            ->success()
            ->body(Str::inlineMarkdown(__('filament-accounts::default.notifications.account_created.body', compact('name'))))
            ->send();
    }

}
