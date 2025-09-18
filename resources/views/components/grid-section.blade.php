@props(['title','description'])
<x-filament::grid @class(["pt-6 gap-4 filament-accounts-grid-section"]) {{ $attributes }}>

    <x-filament::grid.column>
        <h3 @class(['text-lg font-medium filament-accounts-grid-title'])>{{$title}}</h3>

        <p @class(['mt-1 text-sm text-gray-500 filament-accounts-grid-description'])>
            {{$description}}
        </p>
    </x-filament::grid.column>

    <x-filament::grid.column>
        {{ $slot }}
    </x-filament::grid.column>

</x-filament::grid>
