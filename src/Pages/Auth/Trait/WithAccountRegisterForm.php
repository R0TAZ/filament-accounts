<?php

namespace Rotaz\FilamentAccounts\Pages\Auth\Trait;

use App\Models\Account;
use App\Models\Company;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

trait WithAccountRegisterForm
{

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($this->getWizardFormSchema())
                    ->statePath('data'),
            ),
        ];
    }

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

    protected function handleRegistration(array $data): Model
    {
        return $this->create($data);
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        return $data;
    }

    public function create(array $input): ?User
    {
        Log::debug('Call create .. ', $input);

        try {

            return DB::transaction(function () use ($input) {
                return tap(User::create([
                    'name' => strtoupper(data_get($input, 'company_contact')),
                    'email' => data_get($input, 'email'),
                    'phone' => data_get($input, 'phone'),
                    'password' => Hash::make($input['password']),
                ]), function (User $user) use ($input) {
                    $this->createAccount($user, $input);
                });
            });

        } catch (\Throwable $exception) {
            Log::error('Erro create ' . $exception->getMessage());

        }

        return null;

    }

    /**
     * Create a personal account for the user.
     */
    protected function createAccount(User $user, array $formData): void
    {
        Log::debug('Call createCompany .. ', $formData);

        $user->ownedAccounts()->save(Account::forceCreate([
            'user_id' => $user->id,
            'account_type' => data_get($formData, 'account_type'),
            'document' => data_get($formData, 'document'),
            'tenant' => Str::ulid(),
            'name' => strtoupper(data_get($formData, 'company_name')),
            'personal_company' => true,
        ]));
    }
}
