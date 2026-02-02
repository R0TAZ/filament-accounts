<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Rotaz\FilamentAccounts\Enums\SubscriptionCycle;
use Rotaz\FilamentAccounts\Enums\SubscriptionStatus;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\RedirectsActions;
use Throwable;

class BillingCheckoutForm extends Component implements HasActions, HasForms
{
    use CanUseDatabaseTransactions;
    use InteractsWithActions;
    use InteractsWithForms;
    use RedirectsActions;

    public ?array $data = [];

    public function mount($plan = null): void
    {
        Log::debug('Received plan', [
            $plan,
        ]);

        $this->data = $plan;
        $this->form->fill($this->data);

    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make()
                    ->label('Formas de pagamento')
                    ->schema([
                        ToggleButtons::make('payment')
                            ->hiddenLabel()
                            ->inline()
                            ->options([
                                'pix' => 'PIX',
                                'link' => 'LINK',
                                'credit-card' => 'CARTÃO DE CREDITO',
                                'boleto' => 'BOLETO BANCARIO',
                            ])
                            ->icons([
                                'pix' => 'heroicon-o-qr-code',
                                'link' => 'heroicon-o-link',
                                'credit-card' => 'heroicon-o-credit-card',
                            ]),

                    ])])->statePath('data');

    }

    public function render()
    {
        return view('filament-accounts::livewire.billing-checkout-form');
    }

    public function getFormActions(): array
    {
        return [
            $this->getSubscriptionSaveFormAction(),
            $this->getSubscriptionBackFormAction(),
        ];
    }

    public function save(): void
    {
        try {

            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            $data = $this->mutateFormDataBeforeSave($this->data);

            $record = $this->handleRecordSave($data);

        } catch (Halt $exception) {
            Log::error($exception->getMessage());
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->getSavedNotification('Sua subscrição foi criada com sucesso')?->send();

        $this->redirect(filament()->getTenantBillingUrl());

    }

    protected function getSavedNotification($message): ?Notification
    {

        return Notification::make()
            ->success()
            ->icon('heroicon-o-clock')
            ->body($message)
            ->title('Subscrição');
    }

    protected function handleRecordSave(array $data): Model
    {
        Log::debug('Saving handleRecordSave ', [
            'data' => $data,
        ]);

        $record = FilamentAccounts::subscriptionModel()::create($data);
        $record->createInvoices();

        return $record;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::debug('BillingCheckout mutateFormDataBeforeSave', [
            $data,
        ]);

        $end_at = $data['cycle'] == SubscriptionCycle::YEAR ? Carbon::now()->addYear() : Carbon::now()->addMonth();

        return [
            'billable_type' => FilamentAccounts::subscriberModel(),
            'billable_id' => FilamentAccounts::getSessionSubscriber()->getKey(),
            'billing_plan_id' => $data['billing_plan_id'],
            'vendor_slug' => 'rotaz',
            'cycle' => $data['cycle'],
            'amount' => $data['amount'],
            'status' => SubscriptionStatus::ACTIVE,
            'seats' => 1,
            'ends_at' => $end_at,
        ];
    }

    public function back(): Response | Redirector | RedirectResponse
    {
        return redirect(redirect()->back()->getTargetUrl());
    }

    public function getSubscriptionSaveFormAction(): Action
    {
        return Action::make('save')
            ->size(ActionSize::ExtraLarge)
            ->label('SALVAR')
            ->submit('save');
    }

    public function getSubscriptionBackFormAction(): Action
    {
        return Action::make('back')
            ->label('VOLTAR')
            ->action('back');
    }
}
