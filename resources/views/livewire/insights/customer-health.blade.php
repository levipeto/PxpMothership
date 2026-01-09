<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Ügyfél Egészség</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Ügyfelek állapota {{ $year }} évi küldeményszám változása alapján (min. 5 küldemény)
            </flux:text>
        </div>
        <div class="flex gap-2">
            @foreach($this->availableYears as $availableYear)
                <flux:button
                    wire:click="setYear({{ $availableYear }})"
                    :variant="$year === $availableYear ? 'primary' : 'ghost'"
                    size="sm"
                >
                    {{ $availableYear }}
                </flux:button>
            @endforeach
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="flex flex-wrap gap-3 lg:flex-nowrap">
        <div class="flex flex-1 items-center gap-3 rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                <flux:icon.users class="h-4 w-4 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Összes</p>
                <p class="text-xl font-semibold text-zinc-900 dark:text-white">{{ number_format($this->healthStats['total']) }}</p>
            </div>
        </div>

        <div class="flex flex-1 items-center gap-3 rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                <flux:icon.check-circle class="h-4 w-4 text-green-600 dark:text-green-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Egészséges</p>
                <p class="text-xl font-semibold text-green-600 dark:text-green-400">{{ number_format($this->healthStats['healthy']) }}</p>
            </div>
        </div>

        <div class="flex flex-1 items-center gap-3 rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900/30">
                <flux:icon.exclamation-triangle class="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Figyelem</p>
                <p class="text-xl font-semibold text-yellow-600 dark:text-yellow-400">{{ number_format($this->healthStats['warning']) }}</p>
            </div>
        </div>

        <div class="flex flex-1 items-center gap-3 rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30">
                <flux:icon.exclamation-circle class="h-4 w-4 text-orange-600 dark:text-orange-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Veszély</p>
                <p class="text-xl font-semibold text-orange-600 dark:text-orange-400">{{ number_format($this->healthStats['at_risk']) }}</p>
            </div>
        </div>

        <div class="flex flex-1 items-center gap-3 rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                <flux:icon.x-circle class="h-4 w-4 text-red-600 dark:text-red-400" />
            </div>
            <div class="min-w-0">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Kritikus</p>
                <p class="text-xl font-semibold text-red-600 dark:text-red-400">{{ number_format($this->healthStats['critical'] + $this->healthStats['churned']) }}</p>
            </div>
        </div>
    </div>

    {{-- Health Distribution Chart --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Egészség megoszlás</h3>
        <div
            wire:ignore
            x-data="healthChart(@js($this->healthChartOptions))"
            class="h-72"
        >
            <div x-ref="chart" class="h-full w-full"></div>
        </div>
    </div>

    {{-- At-Risk Customers Table --}}
    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center justify-between border-b border-zinc-200 p-4 dark:border-zinc-700">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Figyelmet igénylő ügyfelek</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Csökkenő forgalmú ügyfelek, sürgősség szerint rendezve</p>
            </div>
            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $this->atRiskCustomers->total() }} ügyfél
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50 text-left text-sm font-medium text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-400">
                        <th class="sticky left-0 bg-zinc-50 px-4 py-3 dark:bg-zinc-800/50">Státusz</th>
                        <th class="sticky left-20 bg-zinc-50 px-4 py-3 dark:bg-zinc-800/50">Cégnév</th>
                        @for($m = 1; $m <= 12; $m++)
                            <th class="px-3 py-3 text-right">{{ $this->monthLabels[$m] }}</th>
                        @endfor
                        <th class="px-4 py-3">Utolsó</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($this->atRiskCustomers->items() as $customer)
                        <tr class="text-sm text-zinc-900 hover:bg-zinc-50 dark:text-zinc-100 dark:hover:bg-zinc-800/50">
                            <td class="sticky left-0 bg-white px-4 py-3 dark:bg-zinc-800">
                                @php
                                    $statusConfig = [
                                        'critical' => ['label' => 'Kritikus', 'bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-800 dark:text-red-300'],
                                        'at_risk' => ['label' => 'Veszélyben', 'bg' => 'bg-orange-100 dark:bg-orange-900/30', 'text' => 'text-orange-800 dark:text-orange-300'],
                                        'warning' => ['label' => 'Figyelem', 'bg' => 'bg-yellow-100 dark:bg-yellow-900/30', 'text' => 'text-yellow-800 dark:text-yellow-300'],
                                        'churned' => ['label' => 'Elveszett', 'bg' => 'bg-zinc-100 dark:bg-zinc-700', 'text' => 'text-zinc-800 dark:text-zinc-300'],
                                    ];
                                    $config = $statusConfig[$customer['status']] ?? $statusConfig['warning'];
                                @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                    {{ $config['label'] }}
                                </span>
                            </td>
                            <td class="sticky left-20 bg-white px-4 py-3 font-medium dark:bg-zinc-800">
                                {{ $customer['company_name'] ?: '-' }}
                            </td>
                            @php
                                $formatRevenue = fn($v) => $v >= 1000000 ? number_format($v / 1000000, 1) . 'M' : ($v >= 1000 ? number_format($v / 1000, 0) . 'k' : number_format($v, 0));
                                $months = $customer['months'];
                            @endphp
                            @for($m = 1; $m <= 12; $m++)
                                @php
                                    $count = $months[$m]['count'];
                                    $revenue = $months[$m]['revenue'];
                                    $prevCount = $m > 1 ? $months[$m - 1]['count'] : 0;
                                    $prevRevenue = $m > 1 ? $months[$m - 1]['revenue'] : 0;
                                    $countDiff = $prevCount > 0 ? (($count - $prevCount) / $prevCount) * 100 : 0;
                                    $revDiff = $prevRevenue > 0 ? (($revenue - $prevRevenue) / $prevRevenue) * 100 : 0;
                                @endphp
                                <td class="px-3 py-3 text-right tabular-nums">
                                    @if($count > 0 || $revenue > 0)
                                        <div class="flex flex-col items-end gap-0.5">
                                            <div>
                                                {{ $count }}
                                                @if($m > 1 && $prevCount > 0)
                                                    <span class="text-xs {{ $countDiff < 0 ? 'text-red-500' : 'text-green-500' }}">({{ $countDiff > 0 ? '+' : '' }}{{ number_format($countDiff, 0) }}%)</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $formatRevenue($revenue) }}
                                                @if($m > 1 && $prevRevenue > 0)
                                                    <span class="{{ $revDiff < 0 ? 'text-red-500' : 'text-green-500' }}">({{ $revDiff > 0 ? '+' : '' }}{{ number_format($revDiff, 0) }}%)</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-zinc-300">-</span>
                                    @endif
                                </td>
                            @endfor
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($customer['last_shipment'])
                                    <span class="text-zinc-600 dark:text-zinc-400">
                                        {{ $customer['days_since_shipment'] }}d
                                    </span>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                Nincs figyelmet igénylő ügyfél
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($this->atRiskCustomers->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $this->atRiskCustomers->links() }}
            </div>
        @endif
    </div>
</div>

@script
<script>
    Alpine.data('healthChart', (options) => ({
        chart: null,
        options: options,

        init() {
            this.$nextTick(() => {
                if (!this.$refs.chart) return;
                this.chart = echarts.init(this.$refs.chart, null, { renderer: 'canvas' });
                this.chart.setOption(this.options);

                window.addEventListener('resize', () => {
                    if (this.chart) this.chart.resize();
                });
            });
        },

        destroy() {
            if (this.chart) {
                this.chart.dispose();
                this.chart = null;
            }
        }
    }));
</script>
@endscript
