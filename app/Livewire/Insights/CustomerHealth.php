<?php

namespace App\Livewire\Insights;

use App\Models\Legacy\Kuldemeny;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Ügyfél Egészség')]
class CustomerHealth extends Component
{
    use WithPagination;

    private const MIN_PREVIOUS_SHIPMENTS = 5;
    private const PER_PAGE = 25;

    public int $year;

    public function mount(): void
    {
        $this->year = Carbon::now()->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
        $this->resetPage();
        unset($this->customerHealthData, $this->healthStats, $this->atRiskCustomers, $this->monthLabels, $this->healthChartOptions);
    }

    #[Computed]
    public function availableYears(): array
    {
        return [2025, 2026];
    }

    #[Computed]
    public function customerHealthData(): Collection
    {
        $year = $this->year;
        $minShipments = self::MIN_PREVIOUS_SHIPMENTS;

        // Build month ranges for Jan-Dec
        $monthSelects = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1)->startOfMonth()->format('Y-m-d');
            $end = Carbon::create($year, $m, 1)->endOfMonth()->format('Y-m-d');
            $monthSelects[] = DB::raw("SUM(CASE WHEN k_kiszallitas_datum >= '{$start}' AND k_kiszallitas_datum <= '{$end}' THEN 1 ELSE 0 END) as m{$m}_count");
            $monthSelects[] = DB::raw("SUM(CASE WHEN k_kiszallitas_datum >= '{$start}' AND k_kiszallitas_datum <= '{$end}' THEN k_fd_netto ELSE 0 END) as m{$m}_revenue");
        }

        $yearStart = Carbon::create($year, 1, 1)->format('Y-m-d');

        return Kuldemeny::query()
            ->notDeleted()
            ->select(
                'k_ugyfelkod',
                'k_uf_ceg_nev',
                DB::raw('MAX(k_kiszallitas_datum) as last_shipment'),
                ...$monthSelects
            )
            ->whereDate('k_kiszallitas_datum', '>=', $yearStart)
            ->groupBy('k_ugyfelkod', 'k_uf_ceg_nev')
            ->having(DB::raw('COUNT(*)'), '>=', $minShipments)
            ->get()
            ->map(function ($customer) use ($year) {
                $lastShipment = $customer->last_shipment ? Carbon::parse($customer->last_shipment) : null;
                $isCurrentYear = $year === Carbon::now()->year;

                // For current year, use current month; for past years, use December
                $referenceMonth = $isCurrentYear ? Carbon::now()->month : 12;
                // For calculating "days since", use now() for current year, end of year for past years
                $referenceDate = $isCurrentYear ? now() : Carbon::create($year, 12, 31)->endOfDay();

                // Get counts for all months
                $months = [];
                for ($m = 1; $m <= 12; $m++) {
                    $months[$m] = [
                        'count' => (int) $customer->{"m{$m}_count"},
                        'revenue' => (int) $customer->{"m{$m}_revenue"},
                    ];
                }

                // Calculate average of previous months (excluding reference month) for health status
                $previousMonths = array_filter($months, fn ($m, $key) => $key < $referenceMonth, ARRAY_FILTER_USE_BOTH);
                $previousCounts = array_column($previousMonths, 'count');
                $avgPrevious = count($previousCounts) > 0 ? array_sum($previousCounts) / count($previousCounts) : 0;

                $current = $months[$referenceMonth]['count'];
                $change = $avgPrevious > 0 ? (($current - $avgPrevious) / $avgPrevious) * 100 : 0;
                $daysSinceShipment = $lastShipment ? (int) $lastShipment->diffInDays($referenceDate) : null;
                $status = $this->calculateHealthStatus($current, (int) round($avgPrevious), $daysSinceShipment);

                return (object) [
                    'ugyfelkod' => $customer->k_ugyfelkod,
                    'company_name' => $customer->k_uf_ceg_nev,
                    'months' => $months,
                    'avg_previous' => round($avgPrevious, 1),
                    'change_percent' => round($change, 1),
                    'last_shipment' => $lastShipment,
                    'days_since_shipment' => $daysSinceShipment,
                    'status' => $status,
                ];
            });
    }

    private function calculateHealthStatus(int $current, int $previous, ?int $daysSinceShipment): string
    {
        $daysSinceShipment = $daysSinceShipment ?? 999;

        if ($daysSinceShipment >= 60) {
            return 'churned';
        }
        if ($daysSinceShipment >= 30 && $current === 0) {
            return 'critical';
        }

        if ($previous === 0) {
            return 'healthy';
        }

        $change = (($current - $previous) / $previous) * 100;

        return match (true) {
            $change >= -10 => 'healthy',
            $change >= -30 => 'warning',
            $change >= -60 => 'at_risk',
            default => 'critical',
        };
    }

    #[Computed]
    public function healthStats(): array
    {
        $data = $this->customerHealthData;

        $stats = [
            'total' => $data->count(),
            'healthy' => $data->where('status', 'healthy')->count(),
            'warning' => $data->where('status', 'warning')->count(),
            'at_risk' => $data->where('status', 'at_risk')->count(),
            'critical' => $data->where('status', 'critical')->count(),
            'churned' => $data->where('status', 'churned')->count(),
        ];

        return $stats;
    }

    #[Computed]
    public function atRiskCustomers(): LengthAwarePaginator
    {
        $statusOrder = ['critical' => 1, 'at_risk' => 2, 'warning' => 3, 'churned' => 4];

        $filtered = $this->customerHealthData
            ->filter(fn ($c) => in_array($c->status, ['warning', 'at_risk', 'critical', 'churned']))
            ->sortBy(fn ($c) => $statusOrder[$c->status] ?? 99)
            ->map(function ($customer) {
                return [
                    'ugyfelkod' => $customer->ugyfelkod,
                    'company_name' => $customer->company_name,
                    'months' => $customer->months,
                    'avg_previous' => $customer->avg_previous,
                    'change_percent' => $customer->change_percent,
                    'last_shipment' => $customer->last_shipment?->format('Y-m-d'),
                    'days_since_shipment' => $customer->days_since_shipment,
                    'status' => $customer->status,
                ];
            })
            ->values();

        $page = $this->getPage();
        $perPage = self::PER_PAGE;

        return new LengthAwarePaginator(
            $filtered->forPage($page, $perPage),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );
    }

    #[Computed]
    public function monthLabels(): array
    {
        $labels = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[$m] = Carbon::create($this->year, $m, 1)->translatedFormat('M');
        }

        return $labels;
    }

    #[Computed]
    public function healthChartOptions(): array
    {
        $stats = $this->healthStats;

        $data = [
            ['name' => 'Egészséges', 'value' => $stats['healthy'], 'itemStyle' => ['color' => '#22c55e']],
            ['name' => 'Figyelmeztetés', 'value' => $stats['warning'], 'itemStyle' => ['color' => '#eab308']],
            ['name' => 'Veszélyben', 'value' => $stats['at_risk'], 'itemStyle' => ['color' => '#f97316']],
            ['name' => 'Kritikus', 'value' => $stats['critical'], 'itemStyle' => ['color' => '#ef4444']],
            ['name' => 'Elveszett', 'value' => $stats['churned'], 'itemStyle' => ['color' => '#71717a']],
        ];

        // Filter out zero values
        $data = array_filter($data, fn ($item) => $item['value'] > 0);

        if (empty($data)) {
            $data = [['name' => 'Nincs adat', 'value' => 1, 'itemStyle' => ['color' => '#d4d4d8']]];
        }

        return [
            'tooltip' => [
                'trigger' => 'item',
                'formatter' => '{b}: {c} ({d}%)',
            ],
            'legend' => [
                'orient' => 'vertical',
                'left' => 'left',
                'top' => 'center',
            ],
            'series' => [
                [
                    'type' => 'pie',
                    'radius' => ['40%', '70%'],
                    'center' => ['60%', '50%'],
                    'avoidLabelOverlap' => true,
                    'itemStyle' => [
                        'borderRadius' => 4,
                        'borderColor' => '#fff',
                        'borderWidth' => 2,
                    ],
                    'label' => ['show' => false],
                    'emphasis' => [
                        'label' => [
                            'show' => true,
                            'fontSize' => 14,
                            'fontWeight' => 'bold',
                        ],
                    ],
                    'data' => array_values($data),
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.insights.customer-health');
    }
}
