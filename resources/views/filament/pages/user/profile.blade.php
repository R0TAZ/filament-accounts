<x-filament-panels::page>
    @php
        $components = \Rotaz\FilamentAccounts\FilamentAccounts::getProfileComponents();
    @endphp

    @foreach($components as $index => $component)
        @livewire($component)

        @if($loop->remaining)
            <x-filament-accounts::section-border />
        @endif
    @endforeach
</x-filament-panels::page>
