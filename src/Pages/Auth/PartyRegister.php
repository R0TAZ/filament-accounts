<?php

namespace Rotaz\FilamentAccounts\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Concerns\HasRoutes;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Rotaz\FilamentAccounts\Contracts\AddsAccountParties;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\Pages\Auth\Trait\WithAccountRegisterFields;

class PartyRegister extends SimplePage
{
    use CanUseDatabaseTransactions;
    use HasRoutes;
    use InteractsWithFormActions;
    use WithAccountRegisterFields;
    use WithRateLimiting;

    protected static string $view = 'filament-accounts::auth.party-register';

    public bool $hasUser = false;

    public bool $hasSuccess = false;

    public ?array $data = [];

    protected string $userModel;

    public int | string | null $invitationId = null;

    public function mount(int | string | null $h = null): void
    {
        Log::debug('PartyRegister mount called  ', [
            'h' => $h,
        ]);

        $this->hasSuccess = request()->hasValidSignature(false);

        if ($this->hasSuccess) {
            $this->invitationId = decrypt($h, false);
            $invitation = $this->getInvitation();

            if ($invitation) {
                $user = $this->getUserFromEmail($invitation->email);
                $this->data = [
                    'email' => $invitation->email,
                    'account_name' => $invitation->account->name,
                ];

                if ($user && ! empty($user->password)) {
                    $this->hasUser = true;
                    Log::debug('User already exists for invitation', [
                        'user_id' => $user->getKey(),
                        'email' => $user->email,
                    ]);
                }

                $this->callHook('beforeFill');

                $this->form->fill($this->data);

                $this->callHook('afterFill');

            } else {
                $this->hasSuccess = false;
            }
        }

    }

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema(fn () => $this->hasUser ? [
                        $this->getCommonFormComponent($this->generateCommonMessage()),
                        $this->getEmailFormComponent()->readOnly(),
                        $this->getPasswordLoginFormComponent(),
                        $this->getRememberFormComponent(),
                    ] : [
                        $this->getCommonFormComponent($this->generateCommonMessage()),
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent()->readOnly(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function generateCommonMessage(): string
    {
        $info = $this->hasUser ? 'Por favor, faça login para aceitar o convite.' : 'Por favor, preencha o formulário abaixo para criar sua conta e aceitar o convite.';

        return 'Você recebeu um convite da conta ' . ($this->data['account_name'] ?? '') . ".<br/>{$info}";
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    public function getSubmitAction(): string
    {
        return $this->hasUser ? 'authenticate' : 'register';
    }

    protected function getFormActions(): array
    {
        return [! $this->hasUser ?
            $this->getRegisterFormAction() : $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(__('filament-panels::pages/auth/register.form.actions.register.label'))
            ->submit('register');
    }

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    public function getMaxWidth(): MaxWidth | string | null
    {
        return MaxWidth::TwoExtraLarge;
    }

    public function getView(): string
    {
        if (! $this->hasSuccess) {
            $this->data = [
                'title' => 'Registro de Convite Inválido',
                'description' => 'O link de convite que você está tentando usar é inválido ou expirou. Por favor, solicite um novo convite ou entre em contato com o suporte para obter assistência.',
                'redirect_url' => route('filament.auth.login'),
                'redirect_label' => 'Voltar ao Login',
            ];

            return 'filament-accounts::auth.invite-error';
        }

        return static::$view;
    }

    public static function getSlug(): string
    {
        return 'party/{h?}/register';
    }

    public static function getRouteName(): string
    {
        return 'party.register';
    }

    protected function getUserModel(): string
    {
        if (isset($this->userModel)) {
            return $this->userModel;
        }

        $authGuard = Filament::auth();

        $provider = $authGuard->getProvider();

        return $this->userModel = $provider->getModel();
    }

    protected function getInvitation()
    {
        $model = FilamentAccounts::accountInvitationModel();

        return $model::find($this->invitationId);
    }

    protected function getUserFromEmail(string $email)
    {
        $userModel = $this->getUserModel();

        return $userModel::where('email', $email)->first();
    }

    protected function handleRegistration(array $data): Model
    {
        $invitation = $this->getInvitation();

        return $this->getUserModel()::create([
            'name' => $data['name'],
            'email' => $invitation->email,
            'password' => $data['password'],
        ]);
    }

    public function authenticate()
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getLoginRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        $this->registerUserToAccount($user);

        return redirect(filament()->getHomeUrl());
    }

    public function register()
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRegisterRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function (): Model {

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        $this->registerUserToAccount($user);

        return redirect(filament()->getHomeUrl());
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    protected function getRegisterRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function getLoginRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function registerUserToAccount($user): void
    {
        $invitation = $this->getInvitation();

        if ($invitation) {
            app(AddsAccountParties::class)->add(
                $invitation->account->owner,
                $invitation->account,
                $invitation->email,
                $invitation->role
            );

            $invitation->delete();

            $title = __('filament-accounts::default.banner.account_invitation_accepted', ['account' => $invitation->account->name]);
            $notification = Notification::make()->title(Str::inlineMarkdown($title))->success()->persistent()->send();

            if ($user) {
                Filament::auth()->login($user);
                session()->regenerate();
            }
        }
    }
}
