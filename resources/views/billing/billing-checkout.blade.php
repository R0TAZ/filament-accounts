<x-filament::section
    icon="heroicon-m-identification"
    icon-size="lg">
    <x-slot name="heading">
        Definir o plano
    </x-slot>

    <div x-data="{
            billing_cycle_available: @entangle('billing_cycle_available'),
            billing_cycle_selected: @entangle('billing_cycle_selected'),
            toggleButtonClicked(el, month_or_year){
                this.toggleRepositionMarker(el);
                this.billing_cycle_selected = month_or_year;
            },
            toggleRepositionMarker(toggleButton){
                this.$refs.marker.style.width=toggleButton.offsetWidth + 'px';
                this.$refs.marker.style.height=toggleButton.offsetHeight + 'px';
                this.$refs.marker.style.left=toggleButton.offsetLeft + 'px';
            },
            fullScreenLoader: false,
            fullScreenLoaderMessage: 'Loading'
        }"
                 @loader-show.window="fullScreenLoader = true"
                 @loader-hide.window="fullScreenLoader = false"
                 @loader-message.window="fullScreenLoaderMessage = event.detail.message"
                 class="flex items-start justify-center w-full h-full rounded-xl">
                <div class="flex flex-col flex-wrap w-full mx-auto lg:max-w-4xl">
                    <filament-accounts::billing.billing_cycle_toggle></filament-accounts::billing.billing_cycle_toggle>
                    <div class="grid grid-cols-3 h-full space-y-5">
                        @foreach($plans as $plan)
                            @php $features = explode(',', $plan->features); @endphp
                            <div
                                {{--  Say that you have a monthly plan that doesn't have a yearly plan, in that case we will hide the place that doesn't have a price_id --}}
                                class="w-full px-3 mx-auto group">
                                <div class="flex flex-col mb-10 h-full bg-white dark:bg-neutral-800 rounded-xl ease-out duration-300 border-2 border-gray-200 dark:border-neutral-700 shadow-sm sm:mb-0 group-hover:border-primary-500">
                                    <div class="p-6 lg:p-8">
                                        <div class="relative text-gray-500 dark:text-neutral-400">
                                        <span lass="text-lg md:text-xl font-semibold rounded-full">
                                            {{ $plan->name }}
                                        </span>
                                        </div>

                                        <div class="my-3 space-y-2 md:my-5">
                                            <div class="relative">
                                                <span class="text-3xl font-bold lg:text-2xl dark:text-neutral-200"><span x-text="billing_cycle_selected == 'month' ? '{{ \Rotaz\FilamentAccounts\Utils\FormatterUtil::format_currency($plan->monthly_price)  }}' : '{{ \Rotaz\FilamentAccounts\Utils\FormatterUtil::format_currency($plan->yearly_price)}}'"></span></span>
                                                <span class="inline-block font-bold text-gray-500 dark:text-neutral-200 -translate-y-0.5 "><span x-text="billing_cycle_selected == 'month' ? '/{{  __('filament-tables::filters/query-builder.operators.date.form.month.label') }}' : '/{{  __('filament-tables::filters/query-builder.operators.date.form.year.label') }}'"></span></span>
                                            </div>
                                            <p class="text-sm leading-7 text-gray-500 dark:text-neutral-300 lg:text-base">{{ $plan->description }}</p>
                                        </div>

                                        <ul class="flex flex-col mt-5">
                                            @foreach($features as $feature)
                                                <li class="mt-1 text-sm">
                                                <span class="flex items-center text-green-500">
                                                    <svg class="w-4 h-4 mr-3 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"></path></svg>
                                                    <span class="text-gray-600 dark:text-neutral-400">
                                                        {{ $feature }}
                                                    </span>
                                                </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="px-6 py-5 mt-auto bg-gray-50 dark:bg-neutral-700 rounded-b-xl">
                                        <div class="flex items-center justify-end w-full">
                                            <div class="relative w-full md:w-auto">

                                                <x-filament::button wire:click="$parent.selectBillingPlan({{ $plan->id }},billing_cycle_selected)" color="success" rounded="md">
                                                    {{__('filament-panels::pages/auth/register.heading')}}
                                                </x-filament::button>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

</x-filament::section>

