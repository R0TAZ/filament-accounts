<?php

namespace Rotaz\FilamentAccounts\Pages\Auth;

use App\Models\Account;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Rotaz\FilamentAccounts\Events\AccountCreated;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\Pages\Auth\Trait\HasWizardRegisterForm;

/**
 * @property Form $form
 */
class Register extends AccountRegister
{
    use HasWizardRegisterForm;

    protected static string $view = 'filament-panels::pages.auth.register';

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getWizardFormSchema())
            ->statePath('data')
            ->model(FilamentAccounts::userModel());
    }

    public function register(): ?RegistrationResponse
    {
        Log::debug('Registering filament account');

        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function (): Model {

            Log::debug('In wrapInDatabaseTransaction');

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function handleRegistration(array $data): Model
    {
        Log::debug('Call handleRegistration ', $data);

        return $this->create($data);
    }

    /**
     * @throws ValidationException
     */
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

    protected function createAccount(User $user, array $formData): void
    {
        Log::debug('Call createAccount .. ', $formData);

        $account = Account::forceCreate([
            'user_id' => $user->id,
            'account_type' => data_get($formData, 'account_type'),
            'document' => data_get($formData, 'document'),
            'tenant' => Str::ulid(),
            'name' => strtoupper(data_get($formData, 'company_name')),
            'personal_account' => true,
        ]);

        AccountCreated::dispatch($account);

        $user->ownedAccounts()->save($account);
    }
}
