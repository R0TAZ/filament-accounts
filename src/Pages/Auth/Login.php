<?php

namespace Rotaz\FilamentAccounts\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as FilamentLogin;
use Rotaz\FilamentAccounts\FilamentAccounts;


class Login extends FilamentLogin
{
    public static string $view = 'filament-accounts::auth.login';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data')
            ->model(FilamentAccounts::userModel());
    }
}
