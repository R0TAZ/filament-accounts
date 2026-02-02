<?php

namespace Rotaz\FilamentAccounts\Pages\Billing;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Rotaz\FilamentAccounts\Actions\PaymentViewAction;
use Rotaz\FilamentAccounts\Contracts\CreatesSubscription;
use Rotaz\FilamentAccounts\Contracts\HasBilling;
use Rotaz\FilamentAccounts\Enums\PaymentMethodType;
use Rotaz\FilamentAccounts\Enums\SubscriptionCycle;
use Rotaz\FilamentAccounts\FilamentAccounts;

class Subscription extends Page implements HasActions, HasForms, HasInfolists, HasTable
{
    use InteractsWithActions;
    use InteractsWithFormActions;
    use InteractsWithForms;
    use InteractsWithInfolists;
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data;

    protected static string $view = 'filament-accounts::filament.pages.billing.subscription';

    public ?Model $subscription = null;

    public ?array $plans = [];

    public function mount()
    {
        $this->subscription = filament()->getTenant()->subscription;

        $this->form->fill();

    }

    public function getTitle(): string
    {
        return __('filament-panels::layout.actions.billing.label');

    }

    public function subscriptionInfolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Grid::make(3)->schema([
                TextEntry::make('plan.name')->label('Name'),
                TextEntry::make('plan.description')->label('Plan description'),
                TextEntry::make('plan.features')
                    ->label('Features')
                    ->badge()
                    ->separator(','),
            ]),
            Infolists\Components\Grid::make(3)->schema([
                TextEntry::make('amount')
                    ->money('BRL'),
                TextEntry::make('ends_at')
                    ->date('d/m/Y'),
                TextEntry::make('status'),
            ]),
            Infolists\Components\Grid::make(3)->schema([
                TextEntry::make('cycle'),
                TextEntry::make('created_at')
                    ->date('d/m/Y H:i'),
                TextEntry::make('updated_at')
                    ->date('d/m/Y H:i'),
            ]),
        ])->record($this->subscription);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Placeholder::make('ended')
                ->hiddenLabel(true)
                ->content('Sua subscrição está vencida, escolha novo plano para renovar')
                ->hidden(fn ($state) => empty($this->subscription) || ! $this->subscription->ended),
            ToggleButtons::make('billing_plan_id')
                ->label('PLANOS')
                ->live()
                ->options($this->getBillingPlans())
                ->view('filament-accounts::components.choicer-form-field')
                ->inline()
                ->required()
                ->columnSpanFull(),
            Forms\Components\Grid::make(3)->schema([
                ToggleButtons::make('cycle')
                    ->label('TIPO')
                    ->inline()
                    ->required()
                    ->options(SubscriptionCycle::class),
                ToggleButtons::make('payment_type')
                    ->label('FORMAS DE PAGAMENTO')
                    ->options(PaymentMethodType::class)
                    ->inline()
                    ->required()
                    ->columnSpan(2),

            ])->visible(fn ($get) => ! empty($get('billing_plan_id'))),

            Checkbox::make('terms')
                ->required()
                ->visible(fn ($get) => ! empty($get('billing_plan_id')))
                ->helperText('O Contrato está acessível no link abaixo')
                ->label('ACEITAR OS TERMOS DO CONTRATO'),
        ])->columns(1)->statePath('data');

    }

    public function getFormActionsAlignment(): Alignment | string
    {
        return Alignment::Center;
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->visible(fn () => ! empty($this->subscription) && ! $this->subscription->ended)
                ->label('Cancelar')
                ->action('cancel'),
        ];
    }

    public function cancel()
    {
        if ($this->subscription) {
            $this->subscription->cancel();
            $this->subscription = null;
            $this->form->fill();
        }

    }

    public function confirm(CreatesSubscription $createsSubscription)
    {
        $data = $this->form->getState();

        try {


            $billingPlan = app(FilamentAccounts::billingPlanModel())->find($data['billing_plan_id']);
            $cycle = SubscriptionCycle::from($data['cycle']);

            $result = tap(filament()->getTenant(), function (HasBilling $billable) use ($billingPlan, $cycle) {

                return app(CreatesSubscription::class)->create($billable, $billingPlan, $cycle);

            });

            $this->form->fill();

            Notification::make()->title('BILLING')->body('Criado com sucesso')->success()->send();

            return redirect(redirect()->back()->getTargetUrl());

        } catch (\Exception $exception) {
            Notification::make()->title('BILLING')->body($exception->getMessage())->send();

        }

    }

    public function getFormActions()
    {
        return [

            Action::make('confirm')
                ->disabled(fn () => empty($this->data['billing_plan_id']))
                ->label('CONFIRMAR')
                ->submit('confirm'),

        ];
    }

    protected function getBillingPlans()
    {

        FilamentAccounts::findBillingPlans()->each(function ($data) {
            $this->plans[$data->id] = $data;
        });

        return $this->plans;

    }

    public function table(Table $table): Table
    {

        return $table
            ->query(fn () => FilamentAccounts::subscriptionInvoiceModel()::where('subscription_id', $this->subscription?->id))
            ->inverseRelationship('invoices')
            ->modifyQueryUsing(fn ($query) => $query)
            ->columns([
                TextColumn::make('invoice_id'),
                TextColumn::make('amount')
                    ->money('BRL'),
                TextColumn::make('due_at')
                    ->date('d/m/Y'),
                IconColumn::make('status'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                PaymentViewAction::make(),

            ])
            ->bulkActions([
                // ...
            ]);
    }
}
