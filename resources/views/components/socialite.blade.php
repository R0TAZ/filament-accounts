@props([
    'errorMessage' => null,
])

<div class="mt-6 filament-accounts-socialite">
    <div class="filament-accounts-socialite-divider flex flex-row items-center justify-between py-4 text-gray-900 dark:text-white">
        <hr class="w-full mr-2">
        {{ __('filament-accounts::default.subheadings.auth.login') }}
        <hr class="w-full ml-2">
    </div>

    @if ($errorMessage)
        <div class="mt-6 text-center text-sm text-danger-600 dark:text-danger-500">{!! $errorMessage !!}</div>
    @endif

    <div class="filament-accounts-socialite-button-container mt-6 flex flex-wrap items-center justify-center gap-6">
        @foreach (\Rotaz\FilamentAccounts\Enums\Provider::cases() as $provider)
            @if ($provider->isEnabled())
                <a href="{{ \Rotaz\FilamentAccounts\FilamentAccounts::generateOAuthRedirectUrl($provider->value) }}"
                   class="filament-accounts-socialite-buttons inline-flex rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:focus:border-primary-500 py-2 px-4 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                    <span class="sr-only">{{ $provider->getLabel() }}</span>
                    <div class="h-6 w-6">
                        {{ $provider->getIconView() }}
                    </div>
                </a>
            @endif
        @endforeach
    </div>
</div>

