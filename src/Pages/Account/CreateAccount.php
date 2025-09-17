<?php

namespace Rotaz\FilamentAccounts\Pages\Account;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\RegisterTenant as FilamentRegisterTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Rotaz\FilamentAccounts\FilamentAccounts;

class CreateAccount extends FilamentRegisterTenant
{
    protected static string $view = 'filament-accounts::filament.pages.accounts.create_account';

    public static function getLabel(): string
    {
        return __('filament-accounts::default.pages.titles.create_account');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('filament-accounts::default.labels.account_name'))
                    ->autofocus()
                    ->maxLength(255)
                    ->required(),
            ])
            ->model(FilamentAccounts::accountModel())
            ->statePath('data');
    }

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

    protected function accountCreated($name): void
    {
        Notification::make()
            ->title(__('filament-accounts::default.notifications.account_created.title'))
            ->success()
            ->body(Str::inlineMarkdown(__('filament-accounts::default.notifications.account_created.body', compact('name'))))
            ->send();
    }
}
