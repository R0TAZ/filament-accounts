<x-filament-panels::page>
    @livewire(\Rotaz\FilamentAccounts\Http\Livewire\UpdateAccountNameForm::class, compact('account'))

    @livewire(\Rotaz\FilamentAccounts\Http\Livewire\AccountPartyManager::class, compact('account'))

    @if (!$account->personal_account && Gate::check('delete', $account))
        <x-filament-accounts::section-border />
        @livewire(\Rotaz\FilamentAccounts\Http\Livewire\DeleteAccountForm::class, compact('account'))
    @endif
</x-filament-panels::page>
