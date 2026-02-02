<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Rotaz\FilamentAccounts\Actions\PaymentViewAction;
use Rotaz\FilamentAccounts\FilamentAccounts;

class ListSubscriptionInvoices extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public mixed $subscription;

    /**
     * Mount the component.
     */
    public function mount(mixed $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => FilamentAccounts::subscriptionInvoiceModel()::where('subscription_id', $this->subscription->id))
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
                PaymentViewAction::make()->label('VER'),
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('filament-billing::livewire.list-subscription-invoices');
    }
}
