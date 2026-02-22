@php
    $modals = \Rotaz\FilamentAccounts\FilamentAccounts::getModals();
@endphp

<x-filament-accounts::grid-section md="2">
    <x-slot name="title">
        {{ 'Excluir subscrição' }}
    </x-slot>

    <x-slot name="description">
        {{ 'Excluir permanentemente essa subscrição' }}
    </x-slot>

    <x-filament::section>
        <div class="grid gap-y-6">
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                Uma vez que uma subscrição é excluída, todos os seus recursos e dados serão permanentemente excluídos. Antes de excluir esta subscrição, por favor, faça o download de qualquer dado ou informação sobre esta empresa que você deseje manter.
            </div>

            <!-- Delete Account Confirmation Modal -->
            <x-filament::modal id="confirmingSubscriptionDeletion" icon="heroicon-o-exclamation-triangle" icon-color="danger" alignment="{{ $modals['alignment'] }}" footer-actions-alignment="{{ $modals['formActionsAlignment'] }}" width="{{ $modals['width'] }}">
                <x-slot name="trigger">
                    <div class="text-left">
                        <x-filament::button color="danger">
                            Excluir subscrição
                        </x-filament::button>
                    </div>
                </x-slot>

                <x-slot name="heading">
                    Excluir subscrição
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

                    <x-filament::button color="danger" wire:click="confirmingSubscriptionDeletion">
                        Excluir subscrição
                    </x-filament::button>
                </x-slot>
            </x-filament::modal>
        </div>
    </x-filament::section>
</x-filament-accounts::grid-section>
