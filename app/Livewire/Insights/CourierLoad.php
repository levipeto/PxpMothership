<?php

namespace App\Livewire\Insights;

use App\Models\Legacy\Futar;
use App\Models\Legacy\Futarrendeles;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Futár Terhelés')]
class CourierLoad extends Component
{
    public ?string $selectedWeekStart = null;

    public ?string $selectedWeekEnd = null;

    public string $loadPeriod = 'weekly';

    public ?string $customDate = null;

    /** @var array<string> */
    public array $selectedGroups = [];

    public ?int $comparisonYear = null;

    public function mount(): void
    {
        $this->selectedWeekStart = Carbon::now()->startOfWeek()->toDateString();
        $this->selectedWeekEnd = Carbon::now()->endOfWeek()->toDateString();
        $this->customDate = Carbon::now()->toDateString();
        $this->comparisonYear = Carbon::now()->year;
    }

    public function selectWeek(string $weekStart): void
    {
        $start = Carbon::parse($weekStart);
        $this->selectedWeekStart = $start->toDateString();
        $this->selectedWeekEnd = $start->copy()->endOfWeek()->toDateString();
        $this->customDate = $start->toDateString();
    }

    public function updatedCustomDate(): void
    {
        if ($this->customDate) {
            $date = Carbon::parse($this->customDate);
            $this->selectedWeekStart = $date->startOfWeek()->toDateString();
            $this->selectedWeekEnd = $date->copy()->endOfWeek()->toDateString();
        }
    }

    public function setLoadPeriod(string $period): void
    {
        $this->loadPeriod = $period;
    }

    public function toggleGroup(string $group): void
    {
        if (in_array($group, $this->selectedGroups)) {
            $this->selectedGroups = array_values(array_diff($this->selectedGroups, [$group]));
        } else {
            $this->selectedGroups[] = $group;
        }
    }

    public function setComparisonYear(int $year): void
    {
        $this->comparisonYear = $year;
    }

    /**
     * @return array<int>
     */
    public function getAvailableComparisonYearsProperty(): array
    {
        $years = Futarrendeles::query()
            ->notDeleted()
            ->whereNotIn('fr_futar', ['0', 'TESZT', ''])
            ->selectRaw('DISTINCT YEAR(fr_felvetel_datum) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        return $years;
    }

    public function resetToCurrentWeek(): void
    {
        $this->selectedWeekStart = Carbon::now()->startOfWeek()->toDateString();
        $this->selectedWeekEnd = Carbon::now()->endOfWeek()->toDateString();
        $this->customDate = Carbon::now()->toDateString();
    }

    public function getSummaryProperty(): array
    {
        $today = Carbon::today();

        $activeCouriers = Futar::notDeleted()->count();

        $todayStats = Futarrendeles::query()
            ->notDeleted()
            ->whereDate('fr_felvetel_datum', $today)
            ->whereNotIn('fr_futar', ['0', 'TESZT', ''])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN fr_statusz = 3 THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN fr_statusz = 2 THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN fr_statusz = 1 THEN 1 ELSE 0 END) as pending
            ')
            ->first();

        $deliveredPercent = $todayStats->total > 0
            ? round(($todayStats->delivered / $todayStats->total) * 100)
            : 0;

        return [
            'activeCouriers' => $activeCouriers,
            'todayOrders' => $todayStats->total ?? 0,
            'delivered' => $todayStats->delivered ?? 0,
            'inProgress' => $todayStats->in_progress ?? 0,
            'pending' => $todayStats->pending ?? 0,
            'deliveredPercent' => $deliveredPercent,
        ];
    }

    public function getCourierStatsProperty(): Collection
    {
        $weekStart = Carbon::parse($this->selectedWeekStart);
        $weekEnd = Carbon::parse($this->selectedWeekEnd);
        $daysInPeriod = $weekStart->diffInDays($weekEnd) + 1;

        return Futarrendeles::query()
            ->notDeleted()
            ->whereDate('fr_felvetel_datum', '>=', $weekStart)
            ->whereDate('fr_felvetel_datum', '<=', $weekEnd)
            ->whereNotIn('fr_futar', ['0', 'TESZT', ''])
            ->join('futar', 'futarrendeles.fr_futar', '=', 'futar.f_kod')
            ->where('futar.f_torolve', 0)
            ->selectRaw('
                futarrendeles.fr_futar as code,
                futar.f_nev as name,
                COUNT(*) as period_orders,
                SUM(CASE WHEN fr_statusz = 3 THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN fr_statusz = 2 THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN fr_statusz = 1 THEN 1 ELSE 0 END) as pending,
                COUNT(*) as total
            ')
            ->groupBy('futarrendeles.fr_futar', 'futar.f_nev')
            ->orderByDesc('period_orders')
            ->limit(20)
            ->get()
            ->map(function ($courier) use ($daysInPeriod) {
                $courier->success_rate = $courier->total > 0
                    ? round(($courier->delivered / $courier->total) * 100)
                    : 0;

                $avgDaily = $daysInPeriod > 0 ? $courier->period_orders / $daysInPeriod : 0;
                $courier->load_status = $this->calculateLoadStatus((int) round($avgDaily));
                $courier->avg_daily = round($avgDaily, 1);

                return $courier;
            });
    }

    public function getWeeklyTrendProperty(): array
    {
        $startDate = Carbon::now()->subWeeks(11)->startOfWeek();

        $data = Futarrendeles::query()
            ->notDeleted()
            ->whereDate('fr_felvetel_datum', '>=', $startDate)
            ->whereNotIn('fr_futar', ['0', 'TESZT', ''])
            ->selectRaw('
                DATE(DATE_SUB(fr_felvetel_datum, INTERVAL WEEKDAY(fr_felvetel_datum) DAY)) as week_start,
                COUNT(*) as orders,
                SUM(CASE WHEN fr_statusz = 3 THEN 1 ELSE 0 END) as delivered
            ')
            ->groupBy('week_start')
            ->orderBy('week_start')
            ->get();

        $weeks = $data->map(fn ($w) => [
            'start' => $w->week_start,
            'orders' => (int) $w->orders,
            'delivered' => (int) $w->delivered,
            'successRate' => $w->orders > 0 ? round(($w->delivered / $w->orders) * 100) : 0,
            'label' => Carbon::parse($w->week_start)->format('m.d'),
            'isSelected' => $w->week_start === $this->selectedWeekStart,
        ])->toArray();

        return [
            'weeks' => $weeks,
            'maxValue' => $data->max('orders') ?: 1,
        ];
    }

    public function getWeeklyChartOptionsProperty(): array
    {
        $trend = $this->weeklyTrend;
        $weeks = $trend['weeks'];

        $labels = array_column($weeks, 'start');
        $orders = array_column($weeks, 'orders');
        $successRates = array_column($weeks, 'successRate');

        // Calculate 3-week moving average
        $movingAvg = [];
        for ($i = 0; $i < count($orders); $i++) {
            $start = max(0, $i - 2);
            $slice = array_slice($orders, $start, $i - $start + 1);
            $movingAvg[] = count($slice) > 0 ? round(array_sum($slice) / count($slice)) : 0;
        }

        return [
            'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => ['type' => 'shadow'],
            ],
            'legend' => [
                'data' => [__('Rendelések'), __('Mozgóátlag'), __('Siker%')],
                'bottom' => 0,
            ],
            'grid' => [
                'left' => 50,
                'right' => 50,
                'top' => 20,
                'bottom' => 60,
            ],
            'xAxis' => [
                'type' => 'category',
                'data' => $labels,
                'axisLabel' => [
                    'formatter' => '{value}',
                    'rotate' => 0,
                ],
            ],
            'yAxis' => [
                [
                    'type' => 'value',
                    'name' => __('Rendelések'),
                    'position' => 'left',
                ],
                [
                    'type' => 'value',
                    'name' => __('Siker%'),
                    'position' => 'right',
                    'max' => 100,
                    'axisLabel' => ['formatter' => '{value}%'],
                ],
            ],
            'series' => [
                [
                    'name' => __('Rendelések'),
                    'type' => 'bar',
                    'data' => $orders,
                    'itemStyle' => [
                        'color' => '#3b82f6',
                        'borderRadius' => [4, 4, 0, 0],
                    ],
                    'emphasis' => [
                        'itemStyle' => ['color' => '#2563eb'],
                    ],
                ],
                [
                    'name' => __('Mozgóátlag'),
                    'type' => 'line',
                    'data' => $movingAvg,
                    'smooth' => true,
                    'lineStyle' => ['color' => '#f59e0b', 'width' => 2],
                    'itemStyle' => ['color' => '#f59e0b'],
                    'symbol' => 'none',
                ],
                [
                    'name' => __('Siker%'),
                    'type' => 'line',
                    'yAxisIndex' => 1,
                    'data' => $successRates,
                    'smooth' => true,
                    'lineStyle' => ['color' => '#10b981', 'width' => 2, 'type' => 'dashed'],
                    'itemStyle' => ['color' => '#10b981'],
                    'symbol' => 'circle',
                    'symbolSize' => 6,
                ],
            ],
        ];
    }

    public function getDailyTrendForWeekProperty(): array
    {
        $start = Carbon::parse($this->selectedWeekStart);
        $end = Carbon::parse($this->selectedWeekEnd);

        $data = Futarrendeles::query()
            ->notDeleted()
            ->whereDate('fr_felvetel_datum', '>=', $start)
            ->whereDate('fr_felvetel_datum', '<=', $end)
            ->whereNotIn('fr_futar', ['0', 'TESZT', ''])
            ->selectRaw('
                DATE(fr_felvetel_datum) as date,
                COUNT(*) as orders,
                SUM(CASE WHEN fr_statusz = 3 THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN fr_statusz = 2 THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN fr_statusz = 1 THEN 1 ELSE 0 END) as pending
            ')
            ->groupBy(DB::raw('DATE(fr_felvetel_datum)'))
            ->orderBy('date')
            ->get();

        $period = CarbonPeriod::create($start, $end);
        $filledData = collect($period)->map(function ($date) use ($data) {
            $dateStr = $date->toDateString();
            $found = $data->firstWhere('date', $dateStr);

            $orders = $found ? (int) $found->orders : 0;
            $delivered = $found ? (int) $found->delivered : 0;

            return [
                'date' => $dateStr,
                'orders' => $orders,
                'delivered' => $delivered,
                'inProgress' => $found ? (int) $found->in_progress : 0,
                'pending' => $found ? (int) $found->pending : 0,
                'successRate' => $orders > 0 ? round(($delivered / $orders) * 100) : 0,
                'label' => $date->format('D'),
                'fullLabel' => $date->format('m.d (D)'),
            ];
        });

        return [
            'days' => $filledData->toArray(),
            'maxValue' => $filledData->max('orders') ?: 1,
        ];
    }

    public function getDailyChartOptionsProperty(): array
    {
        $daily = $this->dailyTrendForWeek;
        $days = $daily['days'];

        $labels = array_column($days, 'fullLabel');
        $delivered = array_column($days, 'delivered');
        $inProgress = array_column($days, 'inProgress');
        $pending = array_column($days, 'pending');
        $successRates = array_column($days, 'successRate');

        return [
            'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => ['type' => 'shadow'],
            ],
            'legend' => [
                'data' => [__('Teljesítve'), __('Folyamatban'), __('Függőben'), __('Siker%')],
                'bottom' => 0,
            ],
            'grid' => [
                'left' => 50,
                'right' => 50,
                'top' => 20,
                'bottom' => 60,
            ],
            'xAxis' => [
                'type' => 'category',
                'data' => $labels,
            ],
            'yAxis' => [
                [
                    'type' => 'value',
                    'name' => __('Rendelések'),
                    'position' => 'left',
                ],
                [
                    'type' => 'value',
                    'name' => __('Siker%'),
                    'position' => 'right',
                    'max' => 100,
                    'axisLabel' => ['formatter' => '{value}%'],
                ],
            ],
            'series' => [
                [
                    'name' => __('Teljesítve'),
                    'type' => 'bar',
                    'stack' => 'total',
                    'data' => $delivered,
                    'itemStyle' => ['color' => '#10b981', 'borderRadius' => [0, 0, 0, 0]],
                ],
                [
                    'name' => __('Folyamatban'),
                    'type' => 'bar',
                    'stack' => 'total',
                    'data' => $inProgress,
                    'itemStyle' => ['color' => '#3b82f6'],
                ],
                [
                    'name' => __('Függőben'),
                    'type' => 'bar',
                    'stack' => 'total',
                    'data' => $pending,
                    'itemStyle' => ['color' => '#f59e0b', 'borderRadius' => [4, 4, 0, 0]],
                ],
                [
                    'name' => __('Siker%'),
                    'type' => 'line',
                    'yAxisIndex' => 1,
                    'data' => $successRates,
                    'smooth' => true,
                    'lineStyle' => ['color' => '#6366f1', 'width' => 3],
                    'itemStyle' => ['color' => '#6366f1'],
                    'symbol' => 'circle',
                    'symbolSize' => 8,
                ],
            ],
        ];
    }

    public function getStatusDistributionProperty(): array
    {
        $summary = $this->summary;

        return [
            ['name' => __('Teljesítve'), 'value' => $summary['delivered'], 'itemStyle' => ['color' => '#10b981']],
            ['name' => __('Folyamatban'), 'value' => $summary['inProgress'], 'itemStyle' => ['color' => '#3b82f6']],
            ['name' => __('Függőben'), 'value' => $summary['pending'], 'itemStyle' => ['color' => '#f59e0b']],
        ];
    }

    public function getStatusChartOptionsProperty(): array
    {
        $distribution = $this->statusDistribution;
        $total = array_sum(array_column($distribution, 'value'));

        return [
            'tooltip' => [
                'trigger' => 'item',
                'formatter' => '{b}: {c} ({d}%)',
            ],
            'legend' => [
                'orient' => 'horizontal',
                'bottom' => 0,
            ],
            'series' => [
                [
                    'type' => 'pie',
                    'radius' => ['50%', '70%'],
                    'center' => ['50%', '45%'],
                    'avoidLabelOverlap' => false,
                    'label' => [
                        'show' => true,
                        'position' => 'center',
                        'formatter' => "{$total}\n".__('Összesen'),
                        'fontSize' => 16,
                        'fontWeight' => 'bold',
                    ],
                    'emphasis' => [
                        'label' => [
                            'show' => true,
                            'fontSize' => 18,
                            'fontWeight' => 'bold',
                        ],
                    ],
                    'labelLine' => ['show' => false],
                    'data' => $distribution,
                ],
            ],
        ];
    }

    public function getTopPerformersProperty(): Collection
    {
        $monthStart = Carbon::now()->startOfMonth();

        return Futarrendeles::query()
            ->notDeleted()
            ->whereDate('fr_felvetel_datum', '>=', $monthStart)
            ->whereNotIn('fr_futar', ['0', 'TESZT', ''])
            ->join('futar', 'futarrendeles.fr_futar', '=', 'futar.f_kod')
            ->where('futar.f_torolve', 0)
            ->selectRaw('
                futarrendeles.fr_futar as code,
                futar.f_nev as name,
                COUNT(*) as orders,
                SUM(CASE WHEN fr_statusz = 3 THEN 1 ELSE 0 END) as delivered
            ')
            ->groupBy('futarrendeles.fr_futar', 'futar.f_nev')
            ->orderByDesc('orders')
            ->limit(10)
            ->get()
            ->map(function ($performer) {
                $performer->success_rate = $performer->orders > 0
                    ? round(($performer->delivered / $performer->orders) * 100)
                    : 0;

                return $performer;
            });
    }

    public function getAllCouriersLoadProperty(): array
    {
        $now = Carbon::now();

        switch ($this->loadPeriod) {
            case 'monthly':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $periodLabel = $now->format('Y F');
                break;
            case 'annual':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $periodLabel = $now->format('Y');
                break;
            default: // weekly
                $start = Carbon::parse($this->selectedWeekStart);
                $end = Carbon::parse($this->selectedWeekEnd);
                $periodLabel = $start->format('Y.m.d').' - '.$end->format('m.d');
        }

        $data = Futarrendeles::query()
            ->notDeleted()
            ->whereDate('fr_felvetel_datum', '>=', $start)
            ->whereDate('fr_felvetel_datum', '<=', $end)
            ->whereNotIn('fr_futar', ['0', 'TESZT', ''])
            ->join('futar', 'futarrendeles.fr_futar', '=', 'futar.f_kod')
            ->where('futar.f_torolve', 0)
            ->selectRaw('
                futarrendeles.fr_futar as code,
                futar.f_nev as name,
                COUNT(*) as orders
            ')
            ->groupBy('futarrendeles.fr_futar', 'futar.f_nev')
            ->orderByDesc('orders')
            ->limit(30)
            ->get();

        return [
            'couriers' => $data->toArray(),
            'periodLabel' => $periodLabel,
            'total' => $data->sum('orders'),
        ];
    }

    public function getAllCouriersLoadChartOptionsProperty(): array
    {
        $loadData = $this->allCouriersLoad;
        $couriers = $loadData['couriers'];

        $names = array_map(function ($c) {
            $firstName = $this->extractFirstName($c['name']);

            return $firstName ? "{$c['code']} ({$firstName})" : $c['code'];
        }, $couriers);
        $orders = array_map(fn ($c) => $c['orders'], $couriers);

        return [
            'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => ['type' => 'shadow'],
                'formatter' => '{b}: {c} '.__('rendelés'),
            ],
            'grid' => [
                'left' => 80,
                'right' => 30,
                'top' => 20,
                'bottom' => 40,
            ],
            'xAxis' => [
                'type' => 'value',
                'name' => __('Rendelések'),
            ],
            'yAxis' => [
                'type' => 'category',
                'data' => array_reverse($names),
                'axisLabel' => [
                    'fontSize' => 11,
                ],
                'inverse' => false,
            ],
            'dataZoom' => [
                [
                    'type' => 'slider',
                    'yAxisIndex' => 0,
                    'start' => count($couriers) > 20 ? 100 - (20 / count($couriers) * 100) : 0,
                    'end' => 100,
                    'width' => 20,
                    'right' => 5,
                ],
                [
                    'type' => 'inside',
                    'yAxisIndex' => 0,
                ],
            ],
            'series' => [
                [
                    'type' => 'bar',
                    'data' => array_reverse($orders),
                    'itemStyle' => [
                        'color' => '#3b82f6',
                        'borderRadius' => [0, 4, 4, 0],
                    ],
                    'emphasis' => [
                        'itemStyle' => ['color' => '#2563eb'],
                    ],
                    'label' => [
                        'show' => true,
                        'position' => 'right',
                        'fontSize' => 10,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getAvailableCourierGroupsProperty(): array
    {
        $couriers = Futar::notDeleted()
            ->pluck('f_kod')
            ->filter(fn ($code) => ! in_array($code, ['0', 'TESZT', '']))
            ->toArray();

        $prefixes = [];
        foreach ($couriers as $code) {
            preg_match('/^([A-Za-z]+)/', $code, $matches);
            if (! empty($matches[1])) {
                $prefixes[$matches[1]] = true;
            }
        }

        return array_keys($prefixes);
    }

    public function getYearlyMonthlyComparisonProperty(): array
    {
        $year = $this->comparisonYear ?? Carbon::now()->year;
        $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfDay();

        // Build group filter conditions
        $groupConditions = [];
        if (! empty($this->selectedGroups)) {
            foreach ($this->selectedGroups as $group) {
                $groupConditions[] = "futarrendeles.fr_futar LIKE '{$group}%'";
            }
        }

        $query = Futarrendeles::query()
            ->notDeleted()
            ->whereDate('fr_felvetel_datum', '>=', $startOfYear)
            ->whereDate('fr_felvetel_datum', '<=', $endOfYear)
            ->whereNotIn('fr_futar', ['0', 'TESZT', ''])
            ->join('futar', 'futarrendeles.fr_futar', '=', 'futar.f_kod')
            ->where('futar.f_torolve', 0);

        if (! empty($groupConditions)) {
            $query->whereRaw('('.implode(' OR ', $groupConditions).')');
        }

        // Get top 20 couriers first for demo performance
        $topCouriers = (clone $query)
            ->selectRaw('futarrendeles.fr_futar as code, COUNT(*) as total')
            ->groupBy('futarrendeles.fr_futar')
            ->orderByDesc('total')
            ->limit(20)
            ->pluck('code');

        $data = $query
            ->whereIn('futarrendeles.fr_futar', $topCouriers)
            ->selectRaw('
                futarrendeles.fr_futar as code,
                futar.f_nev as name,
                MONTH(fr_felvetel_datum) as month,
                COUNT(*) as orders
            ')
            ->groupBy('futarrendeles.fr_futar', 'futar.f_nev', DB::raw('MONTH(fr_felvetel_datum)'))
            ->orderBy('code')
            ->orderBy('month')
            ->get();

        // Group by courier and create monthly arrays
        $couriers = [];
        foreach ($data as $row) {
            if (! isset($couriers[$row->code])) {
                $couriers[$row->code] = [
                    'code' => $row->code,
                    'name' => $row->name,
                    'months' => array_fill(1, 12, 0),
                    'total' => 0,
                ];
            }
            $couriers[$row->code]['months'][$row->month] = (int) $row->orders;
            $couriers[$row->code]['total'] += (int) $row->orders;
        }

        // Sort by total orders descending
        uasort($couriers, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [
            'couriers' => array_values($couriers),
            'year' => $year,
            'months' => [
                __('Jan'), __('Feb'), __('Már'), __('Ápr'),
                __('Máj'), __('Jún'), __('Júl'), __('Aug'),
                __('Szep'), __('Okt'), __('Nov'), __('Dec'),
            ],
        ];
    }

    public function getYearlyComparisonChartOptionsProperty(): array
    {
        $data = $this->yearlyMonthlyComparison;
        $couriers = $data['couriers'];
        $months = $data['months'];

        // Generate distinct colors for each courier
        $colors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
            '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
            '#14b8a6', '#a855f7', '#22c55e', '#eab308', '#0ea5e9',
            '#d946ef', '#64748b', '#fb7185', '#4ade80', '#facc15',
        ];

        // Build courier info map for tooltip
        $courierInfo = [];
        foreach ($couriers as $courier) {
            $firstName = $this->extractFirstName($courier['name']);
            $label = $firstName ? "{$courier['code']} ({$firstName})" : $courier['code'];
            $courierInfo[$label] = [
                'code' => $courier['code'],
                'name' => $courier['name'] ?: '-',
            ];
        }

        $series = [];
        foreach ($couriers as $index => $courier) {
            $monthData = array_values($courier['months']);
            $firstName = $this->extractFirstName($courier['name']);
            $label = $firstName ? "{$courier['code']} ({$firstName})" : $courier['code'];
            $series[] = [
                'name' => $label,
                'type' => 'line',
                'data' => $monthData,
                'smooth' => true,
                'symbol' => 'circle',
                'symbolSize' => 8,
                'emphasis' => [
                    'focus' => 'series',
                    'lineStyle' => ['width' => 4],
                    'itemStyle' => ['borderWidth' => 2, 'borderColor' => '#fff'],
                ],
                'lineStyle' => [
                    'width' => 2,
                    'color' => $colors[$index % count($colors)],
                ],
                'itemStyle' => [
                    'color' => $colors[$index % count($colors)],
                ],
            ];
        }

        $legendData = array_map(function ($c) {
            $firstName = $this->extractFirstName($c['name']);

            return $firstName ? "{$c['code']} ({$firstName})" : $c['code'];
        }, $couriers);

        return [
            'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => ['type' => 'cross'],
                'confine' => true,
            ],
            'courierInfo' => $courierInfo,
            'courierCount' => count($couriers),
            'legend' => [
                'type' => 'plain',
                'top' => 340,
                'left' => 'center',
                'data' => $legendData,
                'width' => '95%',
                'itemGap' => 8,
                'itemWidth' => 10,
                'itemHeight' => 8,
                'textStyle' => [
                    'fontSize' => 9,
                ],
            ],
            'grid' => [
                'left' => 60,
                'right' => 30,
                'top' => 10,
                'bottom' => 'auto',
                'height' => 300,
            ],
            'xAxis' => [
                'type' => 'category',
                'data' => $months,
                'boundaryGap' => false,
            ],
            'yAxis' => [
                'type' => 'value',
            ],
            'series' => $series,
        ];
    }

    private function calculateLoadStatus(int $todayOrders): string
    {
        if ($todayOrders >= 40) {
            return 'high';
        }

        if ($todayOrders >= 20) {
            return 'normal';
        }

        return 'low';
    }

    private function extractFirstName(?string $fullName): ?string
    {
        if (empty($fullName)) {
            return null;
        }

        // Hungarian names are "FamilyName GivenName", so first part is the family name
        $parts = explode(' ', trim($fullName));

        return $parts[0] ?? null;
    }

    public function getSelectedPeriodLabelProperty(): string
    {
        $start = Carbon::parse($this->selectedWeekStart);
        $end = Carbon::parse($this->selectedWeekEnd);

        return $start->format('Y.m.d').' - '.$end->format('m.d');
    }

    public function render()
    {
        return view('livewire.insights.courier-load', [
            'summary' => $this->summary,
            'courierStats' => $this->courierStats,
            'weeklyTrend' => $this->weeklyTrend,
            'weeklyChartOptions' => $this->weeklyChartOptions,
            'dailyTrendForWeek' => $this->dailyTrendForWeek,
            'dailyChartOptions' => $this->dailyChartOptions,
            'statusChartOptions' => $this->statusChartOptions,
            'topPerformers' => $this->topPerformers,
            'selectedPeriodLabel' => $this->selectedPeriodLabel,
            'allCouriersLoad' => $this->allCouriersLoad,
            'allCouriersLoadChartOptions' => $this->allCouriersLoadChartOptions,
            'availableCourierGroups' => $this->availableCourierGroups,
            'yearlyMonthlyComparison' => $this->yearlyMonthlyComparison,
            'yearlyComparisonChartOptions' => $this->yearlyComparisonChartOptions,
            'availableComparisonYears' => $this->availableComparisonYears,
        ]);
    }
}
