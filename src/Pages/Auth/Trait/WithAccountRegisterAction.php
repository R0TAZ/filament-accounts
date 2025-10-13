<?php

namespace Rotaz\FilamentAccounts\Pages\Auth\Trait;

use App\Models\Account;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait WithAccountRegisterAction
{
    public function register(): ?RegistrationResponse
    {
        Log::debug('Registering filament accounts ');

        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {

            Log::debug('In wrapInDatabaseTransaction...');

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
                    $this->createCompany($user, $input);
                });
            });

        } catch (\Throwable $exception) {
            Log::error('Erro create ' . $exception->getMessage());

        }

        return null;

    }

    protected function createCompany(User $user, array $formData): void
    {
        Log::debug('Call createCompany .. ', $formData);

        $user->ownedCompanies()->save(Account::forceCreate([
            'user_id' => $user->id,
            'account_type' => data_get($formData, 'account_type'),
            'document' => data_get($formData, 'document'),
            'tenant' => Str::ulid(),
            'name' => strtoupper(data_get($formData, 'company_name')),
            'personal_company' => true,
        ]));
    }
}
