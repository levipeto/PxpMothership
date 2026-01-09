<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Futár Terhelés') }}</flux:heading>
            <flux:subheading>{{ __('Heti áttekintés és futár teljesítmény') }}</flux:subheading>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <flux:input
                    type="date"
                    wire:model.live="customDate"
                    class="w-40"
                />
            </div>
            <button
                wire:click="resetToCurrentWeek"
                class="rounded-lg bg-zinc-100 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                {{ __('Ma') }}
            </button>
            <div class="rounded-lg bg-blue-50 px-4 py-2 dark:bg-blue-900/30">
                <p class="text-xs text-blue-600 dark:text-blue-400">{{ __('Kiválasztott hét') }}</p>
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">{{ $selectedPeriodLabel }}</p>
            </div>
        </div>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
        {{-- Active Couriers --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50">
                    <flux:icon name="users" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 lg:text-sm">{{ __('Aktív Futárok') }}</p>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white lg:text-2xl">{{ $summary['activeCouriers'] }}</p>
                </div>
            </div>
        </div>

        {{-- Today's Orders --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
                    <flux:icon name="clipboard-document-list" class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 lg:text-sm">{{ __('Mai Rendelések') }}</p>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white lg:text-2xl">{{ $summary['todayOrders'] }}</p>
                </div>
            </div>
        </div>

        {{-- Delivered Today --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50">
                    <flux:icon name="check-circle" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 lg:text-sm">{{ __('Teljesítve') }}</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 lg:text-2xl">
                        {{ $summary['delivered'] }}
                        <span class="text-sm font-normal text-zinc-500">({{ $summary['deliveredPercent'] }}%)</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Pending Today --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/50">
                    <flux:icon name="clock" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 lg:text-sm">{{ __('Függőben') }}</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400 lg:text-2xl">
                        {{ $summary['pending'] + $summary['inProgress'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- All Couriers Load Chart --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-zinc-900 dark:text-white lg:text-lg">{{ __('Futár Terhelés') }}</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 lg:text-sm">
                    <span wire:loading.remove wire:target="setLoadPeriod">
                        {{ $allCouriersLoad['periodLabel'] }} - {{ __('Összesen') }}: <span class="font-semibold">{{ number_format($allCouriersLoad['total']) }}</span> {{ __('rendelés') }}
                    </span>
                    <span wire:loading wire:target="setLoadPeriod" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Betöltés...') }}
                    </span>
                </p>
            </div>
            <div class="flex rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
                <button
                    wire:click="setLoadPeriod('weekly')"
                    wire:loading.attr="disabled"
                    wire:target="setLoadPeriod"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition disabled:opacity-50 {{ $loadPeriod === 'weekly' ? 'bg-white text-zinc-900 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}"
                >
                    {{ __('Heti') }}
                </button>
                <button
                    wire:click="setLoadPeriod('monthly')"
                    wire:loading.attr="disabled"
                    wire:target="setLoadPeriod"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition disabled:opacity-50 {{ $loadPeriod === 'monthly' ? 'bg-white text-zinc-900 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}"
                >
                    {{ __('Havi') }}
                </button>
                <button
                    wire:click="setLoadPeriod('annual')"
                    wire:loading.attr="disabled"
                    wire:target="setLoadPeriod"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition disabled:opacity-50 {{ $loadPeriod === 'annual' ? 'bg-white text-zinc-900 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}"
                >
                    {{ __('Éves') }}
                </button>
            </div>
        </div>
        <div class="relative">
            {{-- Loading overlay --}}
            <div
                wire:loading
                wire:target="setLoadPeriod"
                class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80"
            >
                <div class="flex flex-col items-center gap-3">
                    <svg class="h-10 w-10 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Adatok betöltése...') }}</span>
                </div>
            </div>
            <div
                wire:ignore
                x-data="echart(@js($allCouriersLoadChartOptions))"
                x-init="init()"
                wire:key="all-couriers-chart-{{ $loadPeriod }}-{{ $selectedWeekStart }}"
                class="h-[500px] lg:h-[600px]"
            >
                <div x-ref="chart" class="h-full w-full"></div>
            </div>
        </div>
    </div>

    {{-- Yearly Monthly Comparison Chart --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
        <div class="mb-4 flex flex-col gap-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white lg:text-lg">{{ __('Éves Összehasonlítás') }}</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 lg:text-sm">
                        <span wire:loading.remove wire:target="toggleGroup, setComparisonYear, $set">
                            {{ $yearlyMonthlyComparison['year'] }} - {{ __('Havi bontás futáronként') }}
                            @if(count($selectedGroups) > 0)
                                <span class="font-medium text-blue-600 dark:text-blue-400">
                                    ({{ implode(', ', $selectedGroups) }})
                                </span>
                            @endif
                        </span>
                        <span wire:loading wire:target="toggleGroup, setComparisonYear, $set" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('Betöltés...') }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
                        @foreach($availableComparisonYears as $year)
                            <button
                                wire:click="setComparisonYear({{ $year }})"
                                wire:loading.attr="disabled"
                                wire:target="setComparisonYear, toggleGroup, $set"
                                class="rounded-md px-3 py-1.5 text-sm font-medium transition disabled:opacity-50 {{ $comparisonYear === $year ? 'bg-white text-zinc-900 shadow dark:bg-zinc-700 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}"
                            >
                                {{ $year }}
                            </button>
                        @endforeach
                    </div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                        <span wire:loading.remove wire:target="toggleGroup, setComparisonYear, $set">
                            {{ count($yearlyMonthlyComparison['couriers']) }} {{ __('futár') }}
                        </span>
                    </div>
                </div>
            </div>
            {{-- Group Filter Toggles --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Csoportok') }}:</span>
                @foreach($availableCourierGroups as $group)
                    <button
                        wire:click="toggleGroup('{{ $group }}')"
                        wire:loading.attr="disabled"
                        wire:target="toggleGroup, setComparisonYear, $set"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition disabled:opacity-50 {{ in_array($group, $selectedGroups) ? 'bg-blue-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                    >
                        {{ $group }}
                    </button>
                @endforeach
                @if(count($selectedGroups) > 0)
                    <button
                        wire:click="$set('selectedGroups', [])"
                        wire:loading.attr="disabled"
                        wire:target="toggleGroup, setComparisonYear, $set"
                        class="rounded-lg bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-200 disabled:opacity-50 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                    >
                        {{ __('Törlés') }}
                    </button>
                @endif
            </div>
        </div>
        <div class="relative">
            {{-- Loading overlay --}}
            <div
                wire:loading
                wire:target="toggleGroup, setComparisonYear, $set"
                class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80"
            >
                <div class="flex flex-col items-center gap-3">
                    <svg class="h-10 w-10 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Adatok betöltése...') }}</span>
                </div>
            </div>
            @if(count($yearlyMonthlyComparison['couriers']) > 0)
                @php
                    $courierCount = count($yearlyMonthlyComparison['couriers']);
                    // Estimate ~12 items per row at 95% width, 14px per row
                    $legendRows = ceil($courierCount / 12);
                    $legendHeight = $legendRows * 14;
                    $chartHeight = 350 + $legendHeight;
                @endphp
                <div
                    wire:ignore
                    x-data="yearlyComparisonChart(@js($yearlyComparisonChartOptions))"
                    wire:key="yearly-comparison-chart-{{ $comparisonYear }}-{{ implode('-', $selectedGroups) }}"
                    style="height: {{ $chartHeight }}px;"
                >
                    <div x-ref="chart" class="h-full w-full"></div>
                </div>
            @else
                <div class="flex h-64 items-center justify-center text-zinc-500 dark:text-zinc-400">
                    {{ __('Válassz ki legalább egy csoportot a megtekintéshez') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Weekly Trend Chart --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-zinc-900 dark:text-white lg:text-lg">{{ __('Heti Trend') }}</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 lg:text-sm">{{ __('Utolsó 12 hét - kattints egy hétre a részletekért') }}</p>
            </div>
        </div>
        <div
            wire:ignore
            x-data="weeklyTrendChart(@js($weeklyChartOptions), 'selectWeek')"
            class="h-64 lg:h-80"
        >
            <div x-ref="chart" class="h-full w-full"></div>
        </div>
    </div>

    {{-- Daily Breakdown for Selected Week --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white lg:text-lg">
                {{ __('Napi bontás') }}: {{ $selectedPeriodLabel }}
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 lg:text-sm">{{ __('Teljesítve / Folyamatban / Függőben állapot szerinti bontás') }}</p>
        </div>
        <div
            wire:ignore
            x-data="echart(@js($dailyChartOptions))"
            x-init="init()"
            wire:key="daily-chart-{{ $selectedWeekStart }}"
            class="h-56 lg:h-72"
        >
            <div x-ref="chart" class="h-full w-full"></div>
        </div>
    </div>

    {{-- Sidebar: Status Donut + Top Performers --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Today's Status Donut --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:p-6">
            <h3 class="mb-4 text-base font-semibold text-zinc-900 dark:text-white">{{ __('Mai állapot') }}</h3>
            <div
                wire:ignore
                x-data="echart(@js($statusChartOptions))"
                x-init="init()"
                class="h-48"
            >
                <div x-ref="chart" class="h-full w-full"></div>
            </div>
        </div>

        {{-- Top 10 Performers --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 p-4 dark:border-zinc-700 lg:px-6 lg:py-4">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">{{ __('Top 10') }}</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Havi teljesítmény') }}</p>
            </div>
            <div class="grid grid-cols-1 divide-y divide-zinc-100 dark:divide-zinc-800 sm:grid-cols-2 sm:divide-y-0">
                @forelse($topPerformers as $index => $performer)
                    <div class="flex items-center gap-3 px-4 py-3 lg:px-6 {{ $index % 2 === 0 ? 'sm:border-r sm:border-zinc-100 sm:dark:border-zinc-800' : '' }}" wire:key="performer-{{ $performer->code }}">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold
                            @if($index === 0) bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300
                            @elseif($index === 1) bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300
                            @elseif($index === 2) bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300
                            @else bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 @endif">
                            {{ $index + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $performer->code }}</p>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $performer->name ?: '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ number_format($performer->orders) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                        {{ __('Nincs adat') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
