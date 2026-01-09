<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Futár Lefedettség') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Irányítószám alapú lefedettségi térkép') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchPostalCode"
                    placeholder="{{ __('Irányítószám keresése...') }}"
                    class="w-48 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                />
                @if($searchPostalCode)
                    <button
                        wire:click="$set('searchPostalCode', '')"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Search Results --}}
    @if($searchPostalCode && strlen($searchPostalCode) >= 4)
        @php $searchResults = $this->searchPostalCodeCoverage(); @endphp
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <h3 class="mb-2 font-semibold text-blue-900 dark:text-blue-100">
                {{ __('Keresési eredmények:') }} {{ $searchPostalCode }}
            </h3>
            @if(count($searchResults) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($searchResults as $result)
                        <span
                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium text-white"
                            style="background-color: {{ $result['color'] }}"
                        >
                            {{ $result['code'] }}
                            @if($result['name'])
                                <span class="opacity-80">({{ $result['name'] }})</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('Nincs futár ehhez az irányítószámhoz') }}</p>
            @endif
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-4">
        {{-- Map --}}
        <div class="lg:col-span-3">
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div
                    wire:ignore
                    x-data="courierCoverageMap({
                        coverageData: @js($this->coverageData),
                        postalCodeLookup: @js($this->postalCodeLookup),
                        selectedCourier: @js($selectedCourier)
                    })"
                    x-on:courier-selected.window="handleCourierSelection($event.detail)"
                    class="h-[800px] w-full rounded-xl"
                >
                    <div x-ref="map" class="h-full w-full rounded-xl"></div>
                </div>
            </div>
        </div>

        {{-- Courier List --}}
        <div class="lg:col-span-1">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">{{ __('Futárok') }}</h3>
                <div class="max-h-[740px] space-y-1 overflow-y-auto">
                    @foreach($this->coverageData as $courier)
                        <button
                            wire:click="selectCourier('{{ $courier['courier_code'] }}')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $selectedCourier === $courier['courier_code'] ? 'bg-zinc-100 dark:bg-zinc-800 ring-2 ring-blue-500' : '' }}"
                        >
                            <span
                                class="h-3 w-3 shrink-0 rounded-full"
                                style="background-color: {{ $courier['color'] }}"
                            ></span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $courier['courier_code'] }}</span>
                            @if($courier['courier_name'])
                                <span class="truncate text-zinc-500 dark:text-zinc-400">{{ $courier['courier_name'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <h3 class="mb-3 font-semibold text-zinc-900 dark:text-white">{{ __('Jelmagyarázat') }}</h3>
        <div class="flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="h-4 w-4 rounded border-2 border-blue-500 bg-blue-500/30"></div>
                <span class="text-zinc-600 dark:text-zinc-400">{{ __('Budapest (részletes határok)') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-4 w-4 rounded-full border-2 border-green-500 bg-green-500/30"></div>
                <span class="text-zinc-600 dark:text-zinc-400">{{ __('Vidék (körök)') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span class="text-zinc-600 dark:text-zinc-400">{{ __('Kattints egy területre a futár megtekintéséhez') }}</span>
            </div>
        </div>
    </div>
</div>
