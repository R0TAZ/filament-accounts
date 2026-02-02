<x-filament-panels::page>
        @if( empty($this->subscription) || $this->subscription->ended )
                <x-filament-panels::form id="form" wire:submit="confirm">
                        {{ $this->form }}

                        <x-filament-panels::form.actions
                                :actions="$this->getCachedFormActions()"
                                :full-width="$this->hasFullWidthFormActions()"
                        />
                </x-filament-panels::form>
        @else
                {{ $this->subscriptionInfolist }}
                {{ $this->subscription->ended }}
                {{ $this->table }}
        @endif

</x-filament-panels::page>
