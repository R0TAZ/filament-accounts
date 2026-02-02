@php
    $gridDirection = $getGridDirection() ?? 'column';
    $hasInlineLabel = $hasInlineLabel();
    $id = $getId();
    $isDisabled = $isDisabled();
    $isInline = $isInline();
    $isMultiple = $isMultiple();
    $statePath = $getStatePath();
    $areButtonLabelsHidden = $areButtonLabelsHidden();
    $options = $getOptions();
    $plunk = \Illuminate\Support\Arr::pluck($options, 'name','id');

@endphp

<x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
        :has-inline-label="$hasInlineLabel"
>
    <x-slot
            name="label"
            @class([
                'sm:pt-1.5' => $hasInlineLabel,
            ])
    >
        {{ $getLabel() }}
    </x-slot>

    <x-filament::grid
            :default="$getColumns('default')"
            :sm="$getColumns('sm')"
            :md="$getColumns('md')"
            :lg="$getColumns('lg')"
            :xl="$getColumns('xl')"
            :two-xl="$getColumns('2xl')"
            :is-grid="! $isInline"
            :direction="$gridDirection"
            :attributes="
            \Filament\Support\prepare_inherited_attributes($attributes)
                ->merge($getExtraAttributes(), escape: false)
                ->class([
                    'fi-fo-toggle-buttons gap-3',
                    '-mt-3' => (! $isInline) && ($gridDirection === 'column'),
                    'flex flex-row' => $isInline,
                ])
        "
    >
        @foreach ($plunk as $value => $label)
            @php
                $inputId = "{$id}-{$value}";
                $shouldOptionBeDisabled = $isDisabled || $isOptionDisabled($value, $label);
                $features = explode(',', $options[$value]['features']);
            @endphp

            <div
                    @class([
                        'break-inside-avoid pt-3' => (! $isInline) && ($gridDirection === 'column'),
                    ])
            >
                <input
                        @disabled($shouldOptionBeDisabled)
                        id="{{ $inputId }}"
                        @if (! $isMultiple)
                            name="{{ $id }}"
                        @endif
                        type="{{ $isMultiple ? 'checkbox' : 'radio' }}"
                        value="{{ $value }}"
                {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}"
                {{ $getExtraInputAttributeBag()->class(['peer pointer-events-none absolute opacity-0']) }}
                />

                <x-filament::button
                        :color="$getColor($value)"
                        :disabled="$shouldOptionBeDisabled"
                        :for="$inputId"
                        class="text-center"
                        :icon="$getIcon($value)"
                        :label-sr-only="$areButtonLabelsHidden"
                        tag="label"
                >
                    {{ $label }}
                    <x-filament::section class="mt-2 break-words">

                        <h3
                                {{ $attributes->class(['text-center text-xl font-semibold leading-6 text-gray-950 dark:text-white']) }}
                        >
                            {{ \Rotaz\FilamentAccounts\Utils\FormatterUtil::format_currency( $options[$value][\Rotaz\FilamentAccounts\Enums\SubscriptionCycle::MONTH->getFieldPrefix()]) }}
                        </h3>
                       <x-filament::section.description>
                           {{ \Rotaz\FilamentAccounts\Utils\FormatterUtil::format_currency( $options[$value][\Rotaz\FilamentAccounts\Enums\SubscriptionCycle::YEAR->getFieldPrefix()]) }}
                       </x-filament::section.description>

                        <div
                                {{ $attributes->class(['p-4 break-words text-sm text-gray-500 dark:text-gray-400']) }}
                        >
                            {{$options[$value]['description']}}
                        </div>


                            <ul class="mt-2 break-words text-sm text-gray-500 dark:text-gray-400'">
                                @foreach($features as $feature)
                                    <li class="mt-1 text-sm">
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>


                    </x-filament::section>
                </x-filament::button>
            </div>
        @endforeach
    </x-filament::grid>
</x-dynamic-component>
