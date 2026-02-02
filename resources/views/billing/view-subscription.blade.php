<x-filament-accounts::grid-section md="2">
    <x-slot name="title">
        Detalhes da Subscrição
        <x-filament-accounts::section-border />
    </x-slot>

    <x-slot name="description">
        {{ $this->subscriptionInfolist }}
    </x-slot>


    <x-filament::section>
        <h3 @class(['text-lg font-medium filament-accounts-grid-title'])>Lista das faturas</h3>
        <x-filament-accounts::section-border />
        @livewire(\Rotaz\FilamentAccounts\Http\Livewire\ListSubscriptionInvoices::class , [ 'subscription' => $this->subscription])
    </x-filament::section>


</x-filament-accounts::grid-section>
