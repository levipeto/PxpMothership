<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Feladó Elemzés</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Ügyfél adatok és iparági bontás
            </flux:text>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Total Customers --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <flux:icon.users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Összes ügyfél</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($this->stats['total_customers']) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Enriched Customers --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <flux:icon.check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Feldolgozott</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($this->stats['enriched_customers']) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Pending Enrichment --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon.clock class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Függőben</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($this->stats['pending_enrichment']) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Failed Enrichment --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                    <flux:icon.x-circle class="h-5 w-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Sikertelen</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($this->stats['failed_enrichment']) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Industry Distribution --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Iparági megoszlás</h3>
            <div
                wire:ignore
                x-data="industryChart(@js($this->industryChartOptions))"
                class="h-72"
            >
                <div x-ref="chart" class="h-full w-full"></div>
            </div>
        </div>

        {{-- Company Size Distribution --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Cégméret megoszlás</h3>
            <div
                wire:ignore
                x-data="sizeChart(@js($this->sizeChartOptions))"
                class="h-72"
            >
                <div x-ref="chart" class="h-full w-full"></div>
            </div>
        </div>
    </div>

    {{-- City Distribution --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Top 10 város</h3>
        <div
            wire:ignore
            x-data="cityChart(@js($this->cityChartOptions))"
            class="h-72"
        >
            <div x-ref="chart" class="h-full w-full"></div>
        </div>
    </div>

    {{-- Top Customers Table --}}
    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Top 200 ügyfél (bevétel alapján)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50 text-left text-sm font-medium text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-400">
                        <th class="px-4 py-3">Cégnév</th>
                        <th class="px-4 py-3 text-right">Küldemények</th>
                        <th class="px-4 py-3 text-right">Bevétel</th>
                        <th class="px-4 py-3 text-right">Átlag/csomag</th>
                        <th class="px-4 py-3">Iparág</th>
                        <th class="px-4 py-3">Méret</th>
                        <th class="px-4 py-3">Város</th>
                        <th class="px-4 py-3 text-center">Státusz</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @if(count($this->topCustomers) > 0)
                        {{-- Averages row --}}
                        <tr class="bg-blue-50 text-sm font-medium text-blue-900 dark:bg-blue-900/20 dark:text-blue-100">
                            <td class="px-4 py-3 font-semibold">
                                Összesen (Top 200)
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold">
                                {{ number_format($this->customerAverages['total_shipments']) }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold">
                                {{ number_format($this->customerAverages['total_revenue'], 0, ',', ' ') }} Ft
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold">
                                {{ number_format($this->customerAverages['avg_per_package'], 0, ',', ' ') }} Ft
                            </td>
                            <td class="px-4 py-3" colspan="4"></td>
                        </tr>
                    @endif
                    @forelse($this->topCustomers as $customer)
                        <tr class="text-sm text-zinc-900 hover:bg-zinc-50 dark:text-zinc-100 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 font-medium">
                                {{ $customer['company_name'] ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                {{ number_format($customer['shipment_count']) }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                {{ number_format($customer['total_revenue'], 0, ',', ' ') }} Ft
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                {{ $customer['shipment_count'] > 0 ? number_format($customer['total_revenue'] / $customer['shipment_count'], 0, ',', ' ') : 0 }} Ft
                            </td>
                            <td class="px-4 py-3">
                                @if($customer['industry'])
                                    <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ $customer['industry'] }}
                                    </span>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($customer['size'])
                                    @php
                                        $sizeColors = [
                                            'micro' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
                                            'small' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                            'large' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                        ];
                                        $sizeLabels = [
                                            'micro' => 'Mikro',
                                            'small' => 'Kis',
                                            'medium' => 'Közepes',
                                            'large' => 'Nagy',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $sizeColors[$customer['size']] ?? '' }}">
                                        {{ $sizeLabels[$customer['size']] ?? $customer['size'] }}
                                    </span>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                {{ $customer['city'] ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($customer['enriched'])
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                        <flux:icon.check class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    </span>
                                @else
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                                        <flux:icon.minus class="h-4 w-4 text-zinc-400" />
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                Nincs megjeleníthető adat
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('industryChart', (options) => ({
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

    Alpine.data('sizeChart', (options) => ({
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

    Alpine.data('cityChart', (options) => ({
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
