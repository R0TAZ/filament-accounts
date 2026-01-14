<?php

namespace Rotaz\FilamentAccounts\Pages\Account;

use App\Models\Account;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rotaz\FilamentAccounts\Events\AddingAccount;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\Pages\Auth\Trait\WithAccountRegisterFields;
use Throwable;

class RegisterAccount extends RegisterTenant
{
    use WithAccountRegisterFields;

    protected static string $view = 'filament-accounts::filament.pages.accounts.create_account';

    protected static ?string $slug = 'new-account';

    public static function getLabel(): string
    {
        return 'Criar Nova Conta';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Definir Conta Empresarial';
    }

    public function hasLogo(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): ?string
    {
        if ($this->tenant) {
            return Filament::getUrl($this->tenant);
        }

        return Filament::getUrl(Filament::getTenant());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getGroupAccountFormSchema())
            ->statePath('data')
            ->model($this->getModel());
    }

    public function register(): void
    {
        abort_unless(static::canView(), 404);

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $this->tenant = $this->handleRegistration($data);

            $this->callHook('afterRegister');

        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        Filament::auth()->login($this->tenant->owner);

        session()->regenerate();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
        }
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        $user = Auth::user();

        Gate::forUser($user)->authorize('create', FilamentAccounts::newAccountModel());

        AddingAccount::dispatch($user);

        return $this->createAccount($user, $data);

    }

    protected function createAccount($user, array $formData)
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

        $user?->switchAccount($account);

        return $account;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(__('filament-panels::pages/auth/register.form.actions.register.label'))
            ->submit('register');
    }

    public function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.cancel.label'))
            ->url($this->getRedirectUrl());
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['account_type'] = 'business';

        return $data;
    }
}
