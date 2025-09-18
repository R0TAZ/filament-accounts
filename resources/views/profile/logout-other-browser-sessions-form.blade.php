@php
    $modals = \Rotaz\FilamentAccounts\FilamentAccounts::getModals();
@endphp

<x-filament-accounts::grid-section md="2">
    <x-slot name="title">
        {{ __('filament-accounts::default.grid_section_titles.browser_sessions') }}
    </x-slot>

    <x-slot name="description">
        {{ __('filament-accounts::default.grid_section_descriptions.browser_sessions') }}
    </x-slot>

    <x-filament::section>
        <div class="grid gap-y-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('filament-accounts::default.subheadings.profile.logout_other_browser_sessions') }}
            </p>

            <!-- Browser Sessions -->
            @if (count($this->sessions) > 0)
                @foreach ($this->sessions as $session)
                    <div class="flex items-center">
                        <div class="pe-3">
                            @if ($session->device === 'desktop')
                                <x-heroicon-o-computer-desktop class="h-8 w-8 text-gray-500" />
                            @elseif ($session->device === 'tablet')
                                <x-heroicon-o-device-tablet class="h-8 w-8 text-gray-500" />
                            @else
                                <x-heroicon-o-device-phone-mobile class="h-8 w-8 text-gray-500" />
                            @endif
                        </div>

                        <div class="font-semibold">
                            <div class="text-sm text-gray-800 dark:text-gray-200">
                                {{ $session->os_name ? $session->os_name . ($session->os_version ? ' ' . $session->os_version : '') : 'filament-accounts::default.labels.unknown' }}
                                -
                                {{ $session->client_name ?: 'filament-accounts::default.labels.unknown' }}
                            </div>

                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                {{ $session->ip_address }},

                                @if ($session->is_current_device)
                                    <span class="text-primary-700 dark:text-primary-500">{{ __('filament-accounts::default.labels.this_device') }}</span>
                                @else
                                    <span class="text-gray-400">{{ __('filament-accounts::default.labels.last_active') }}: {{ $session->last_active }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Log Out Other Devices Confirmation Modal -->
            <x-filament::modal id="confirmingLogout" icon="heroicon-o-information-circle" icon-color="primary" alignment="{{ $modals['alignment'] }}" footer-actions-alignment="{{ $modals['formActionsAlignment'] }}" width="{{ $modals['width'] }}">
                <x-slot name="trigger">
                    <div class="text-left">
                        <x-filament::button wire:click="confirmLogout">
                            {{ __('filament-accounts::default.buttons.logout_browser_sessions') }}
                        </x-filament::button>
                    </div>
                </x-slot>

                <x-slot name="heading">
                    {{ __('filament-accounts::default.modal_titles.logout_browser_sessions') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('filament-accounts::default.modal_descriptions.logout_browser_sessions') }}
                </x-slot>

                <x-filament-forms::field-wrapper id="password" statePath="password" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-filament::input.wrapper>
                        <x-filament::input type="password" placeholder="{{ __('filament-accounts::default.fields.password') }}" x-ref="password" wire:model="password" wire:keydown.enter="logoutOtherBrowserSessions" />
                    </x-filament::input.wrapper>
                </x-filament-forms::field-wrapper>

                <x-slot name="footerActions">
                    @if($modals['cancelButtonAction'])
                        <x-filament::button color="gray" wire:click="cancelLogoutOtherBrowserSessions">
                            {{ __('filament-accounts::default.buttons.cancel') }}
                        </x-filament::button>
                    @endif

                    <x-filament::button wire:click="logoutOtherBrowserSessions">
                        {{ __('filament-accounts::default.buttons.logout_browser_sessions') }}
                    </x-filament::button>
                </x-slot>
            </x-filament::modal>
        </div>
    </x-filament::section>
</x-filament-accounts::grid-section>
