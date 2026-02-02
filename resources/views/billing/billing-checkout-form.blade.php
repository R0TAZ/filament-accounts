@php
    $plan = (object)$this->data;
@endphp
    <x-filament::section
        icon="heroicon-m-credit-card"
        icon-size="lg">
        <x-slot name="heading">
            Definir o pagamento
        </x-slot>


            <form id="form" wire:submit="save">
                <div class="flex flex-col m-10 h-full bg-white dark:bg-neutral-800 rounded-xl ease-out duration-300 border-2 border-gray-200 dark:border-neutral-700 shadow-sm sm:mb-0 group-hover:border-primary-500">
                    <div class="p-6 lg:p-8">
                        <div class="relative text-gray-500 dark:text-neutral-400">
                            <span class="text-lg md:text-xl font-semibold rounded-full">
                                {{ $plan->name }}
                            </span>
                        </div>

                        <div class="my-3 space-y-2 md:my-5">
                            <div class="relative">
                                <span class="text-3xl font-bold lg:text-2xl dark:text-neutral-200">{{ format_currency($plan->amount)  }}</span>
                                <span class="inline-block font-bold text-gray-500 dark:text-neutral-200 -translate-y-0.5 "><span x-text="billing_cycle_selected == 'month' ? '/{{  __('filament-tables::filters/query-builder.operators.date.form.month.label') }}' : '/{{  __('filament-tables::filters/query-builder.operators.date.form.year.label') }}'"></span></span>
                            </div>
                            <p class="text-sm leading-7 text-gray-500 dark:text-neutral-300 lg:text-base">{{ $plan->description }}</p>
                        </div>

                        <ul class="flex flex-col mt-5">
                            @foreach($plan->features as $feature)
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

                        <div class="my-3 space-y-2 md:my-5">
                            {{$this->form}}
                        </div>

                        <div class="px-6 py-5 mt-auto bg-gray-50 dark:bg-neutral-700 rounded-b-xl">
                            <div class="flex items-center justify-start w-full">
                                <div class="relative w-full md:w-auto">
                                    {{ $this->getSubscriptionBackFormAction() }}
                                    {{ $this->getSubscriptionSaveFormAction() }}
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </form>

    </x-filament::section>

