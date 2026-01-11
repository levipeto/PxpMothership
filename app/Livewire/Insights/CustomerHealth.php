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

    private const MIN_PREVIOUS_SHIPMENTS = 10;

    private const PER_PAGE = 25;

    private const DEMO_DAYS = 50;

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
        $minShipments = self::MIN_PREVIOUS_SHIPMENTS;
        $demoDays = self::DEMO_DAYS;

        // For demo: simple query - last N days only
        $startDate = now()->subDays($demoDays)->format('Y-m-d');

        return Kuldemeny::query()
            ->notDeleted()
            ->select(
                'k_ugyfelkod',
                'k_uf_ceg_nev',
                DB::raw('MAX(k_kiszallitas_datum) as last_shipment'),
                DB::raw('COUNT(*) as total_shipments')
            )
            ->where('k_kiszallitas_datum', '>=', $startDate)
            ->groupBy('k_ugyfelkod', 'k_uf_ceg_nev')
            ->having('total_shipments', '>=', $minShipments)
            ->orderByDesc('total_shipments')
            ->limit(30)
            ->get()
            ->map(function ($customer) {
                $lastShipment = $customer->last_shipment ? Carbon::parse($customer->last_shipment) : null;
                $daysSinceShipment = $lastShipment ? (int) $lastShipment->diffInDays(now()) : null;

                // Simplified health status based on days since last shipment
                $status = match (true) {
                    $daysSinceShipment === null => 'churned',
                    $daysSinceShipment >= 30 => 'critical',
                    $daysSinceShipment >= 14 => 'at_risk',
                    $daysSinceShipment >= 7 => 'warning',
                    default => 'healthy',
                };

                return (object) [
                    'ugyfelkod' => $customer->k_ugyfelkod,
                    'company_name' => $customer->k_uf_ceg_nev,
                    'months' => [],
                    'avg_previous' => $customer->total_shipments,
                    'change_percent' => 0,
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
        // Simplified for demo - no monthly breakdown
        return [];
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
