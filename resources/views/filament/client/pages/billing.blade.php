<div style="margin-top: 70px;" class="h-screen flex flex-col md:flex-row md:justify-between border-t border-gray-200 dark:border-gray-700">
    <div class="px-6 py-12 dark:bg-gray-950 dark:text-white shadow-sm h-screen">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <x-filament-panels::logo  />
                {{ env('APP_NAME', 'Laravel') }}
            </h1>
            <h2 class="text-lg font-semibold">
                Billing Management
            </h2>
            <div class="flex items-center mt-6 gap-2">
                <div class="text-sm">
                    Managing billing for {{ $tenant->name }}.
                </div>
            </div>
            <x-filament::link href="{{ url(filament()->getCurrentPanel()->getId()) }}" class="mt-6">
                Return to Dashboard
            </x-filament::link>
        </div>
    </div>
    <div class="w-full lg:flex-1 bg-gray-100 dark:bg-gray-800 h-full overflow-y-auto">
        <a href="{{ url(filament()->getCurrentPanel()->getId()) }}" id="topNavReturnLink" class="lg:hidden flex items-center w-full px-4 py-4 bg-white shadow-lg">
            <svg viewBox="0 0 20 20" fill="currentColor" class="arrow-left w-4 h-4 text-gray-400">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            <div class="ml-2 text-gray-600 underline">
                Return to {{ filament()->getBrandName() }}
            </div>
        </a>

        <div class="px-4 my-4 flex flex-col gap-4">

            <x-filament::section heading="Subscribe">
                @if (!$tenant->subscribedPlans()->first())
                    <div class="my-4">
                        <div>
                            <div class="px-6 py-4 bg-gray-200 border border-gray-300 sm:rounded-lg shadow-sm mb-6">
                                <div class="max-w-2xl text-sm text-gray-600">
                                    It looks like your team has no active subscription.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col gap-4">
                    @forelse ($plans as $plan)
                        <x-filament::section
                            :heading="$plan->name"
                            :headerActions="[($this->changePlanAction($plan))(['plan' => $plan])]"
                            :description="$plan->description"
                        >
                            <div>
                               <div>
                                   @if ($plan->isFree())
                                       <span>Free</span>
                                   @else
                                       <span class="text-3xl font-bold">{{ Number::currency($plan->price + $plan->signup_fee, in: $plan->currency) }}</span>
                                       <small>/ {{ $plan->invoice_period > 1 ? $plan->invoice_period : '' }} {{ $plan->invoice_interval }}</small>
                                       @if ($plan->hasTrial())
                                           <br>
                                           <span class="text-gray-400">{{ $plan->trial_period }} {{ $plan->trial_interval }} trial</span>
                                       @endif
                                   @endif
                               </div>
                            </div>
                            <div class="mt-6 flex flex-col gap-2">
                                @foreach ($plan->features as $feature)
                                    <div class="flex justifiy-start gap-2">
                                        <div>
                                            @if (is_numeric($feature->value) || $feature->value == 'true' || $feature->value == 'unlimited')
                                                <svg viewBox="0 0 20 20" fill="currentColor" class="flex-shrink-0 w-5 h-5 text-custom-500">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            @else
                                                <svg viewBox="0 0 20 20" fill="currentColor" class="flex-shrink-0 w-5 h-5 text-gray-400">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $feature->name }}
                                            @if (is_numeric($feature->value) || $feature->value == 'unlimited')
                                                ({{ __(Str::title($feature->value)) }})
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-filament::section>
                    @empty
                        <div>No plans available.</div>
                    @endforelse
                </div>
            </x-filament::section>

            @if ($currentSubscription && $currentSubscription->active())
                <x-filament::section heading="Cancel Subscription">
                    <div class="max-w-xl text-sm text-gray-600">
                        You can cancel your active subscription below.
                    </div>
                    <div class="mt-3">
                        {{ $this->cancelPlanAction }}
                    </div>
                </x-filament::section>
            @endif
        </div>
    </div>

    <x-filament-actions::modals />
</div>
