@php

    $title = $this->data['title'] ?? null;
    $description = $this->data['description'] ?? null;
    $redirectUrl = $this->data['redirect_url'] ?? null;
    $redirectMessage = $this->data['redirect_label'] ?? null;

@endphp
<x-filament-panels::page.simple>
    <x-filament::section>
        <div class="max-w-2xl mx-auto text-center space-y-6">
            <h1 class="text-3xl font-bold">
                {{ $title }}
            </h1>

            <p class="text-lg text-gray-600 dark:text-gray-400">
                {{ $description }}
            </p>

            <x-filament::button tag="a" href="{{ $redirectUrl }}" color="primary">
                {{$redirectMessage}}
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page.simple>