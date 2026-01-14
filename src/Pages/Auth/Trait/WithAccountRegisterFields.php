<?php

namespace Rotaz\FilamentAccounts\Pages\Auth\Trait;

use App\Models\Account;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Joinapi\FilamentUtility\Form\Document;
use Joinapi\FilamentUtility\Form\PersonNameField;
use Joinapi\FilamentUtility\Form\PhoneNumber;
use Rotaz\FilamentAccounts\Utils\FormUtils;

trait WithAccountRegisterFields
{
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::pages/auth/register.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->validationMessages(['email' => 'O EMAIL não é valido.', 'required' => 'O EMAIL é obrigatório.', 'unique' => 'O EMAIL informado já está em uso.'])
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/register.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    protected function getCommonFormSchema(): array
    {
        return [

            $this->getEmailFormComponent()
                ->label('E-MAIL'),
            $this->getPasswordFormComponent()
                ->dehydrateStateUsing(fn ($state) => $state)
                ->label('SENHA'),
            $this->getPasswordConfirmationFormComponent()
                ->dehydrateStateUsing(fn ($state) => $state)
                ->label('CONFIRMAR SENHA'),

        ];

    }

    public function getPersonalAccountFormSchema(): array
    {
        return [
            PersonNameField::make('company_contact')
                ->extraInputAttributes(FormUtils::getTextFormUpper())
                ->label('NOME'),
            Grid::make()
                ->schema([
                    PhoneNumber::make('phone')
                        ->format('(99)99999-9999')
                        ->label('TELEFONE'),
                    Document::make('document')
                        ->mutateDehydratedStateUsing(fn (?string $state): ?string => FormUtils::only_numbers($state))
                        ->unique(Account::class, 'document')
                        ->validationMessages(['document' => 'O CPF informado não é valido.', 'required' => 'O CPF é obrigatório.', 'unique' => 'O CPF informado já está em uso.'])
                        ->cpf()
                        ->label('CPF'),
                ]),

        ];

    }

    public function getGroupAccountFormSchema(): array
    {
        return [
            PersonNameField::make('company_name')
                ->extraInputAttributes(FormUtils::getTextFormUpper())
                ->label('EMPRESA'),
            Grid::make()
                ->schema([
                    Document::make('document')
                        ->mutateDehydratedStateUsing(fn (?string $state): ?string => FormUtils::only_numbers($state))
                        ->unique(Account::class, 'document')
                        ->validationMessages(['document' => 'O CNPJ informado não é valido.', 'required' => 'O CNPJ é obrigatório.', 'unique' => 'O CNPJ informado já está em uso.'])
                        ->cnpj()
                        ->label('CNPJ'),
                    PersonNameField::make('company_contact')
                        ->required()
                        ->extraInputAttributes(FormUtils::getTextFormUpper())
                        ->label('RESPONSAVEL'),
                ]),
        ];

    }
}
