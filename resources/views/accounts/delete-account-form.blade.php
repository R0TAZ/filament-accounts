@php
    $modals = \Rotaz\FilamentAccounts\FilamentAccounts::getModals();
@endphp

<x-filament-accounts::grid-section md="2">
    <x-slot name="title">
        {{ __('filament-accounts::default.action_section_titles.delete_account') }}
    </x-slot>

    <x-slot name="description">
        {{ __('filament-accounts::default.action_section_descriptions.delete_account') }}
    </x-slot>

    <x-filament::section>
        <div class="grid gap-y-6">
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                {{ __('filament-accounts::default.subheadings.accounts.delete_account') }}
            </div>

            <!-- Delete Account Confirmation Modal -->
            <x-filament::modal id="confirmingAccountDeletion" icon="heroicon-o-exclamation-triangle" icon-color="danger" alignment="{{ $modals['alignment'] }}" footer-actions-alignment="{{ $modals['formActionsAlignment'] }}" width="{{ $modals['width'] }}">
                <x-slot name="trigger">
                    <div class="text-left">
                        <x-filament::button color="danger">
                            {{ __('filament-accounts::default.buttons.delete_account') }}
                        </x-filament::button>
                    </div>
                </x-slot>

                <x-slot name="heading">
                    {{ __('filament-accounts::default.modal_titles.delete_account') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('filament-accounts::default.modal_descriptions.delete_account') }}
                </x-slot>

                <x-slot name="footerActions">
                    @if($modals['cancelButtonAction'])
                        <x-filament::button color="gray" wire:click="cancelAccountDeletion">
                            {{ __('filament-accounts::default.buttons.cancel') }}
                        </x-filament::button>
                    @endif

                    <x-filament::button color="danger" wire:click="deleteAccount">
                        {{ __('filament-accounts::default.buttons.delete_account') }}
                    </x-filament::button>
                </x-slot>
            </x-filament::modal>
        </div>
    </x-filament::section>
</x-filament-accounts::grid-section>
