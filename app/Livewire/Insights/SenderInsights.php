<?php

namespace App\Livewire\Insights;

use App\Models\CustomerEnrichment;
use App\Models\Legacy\Kuldemeny;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Feladó Elemzés')]
class SenderInsights extends Component
{
    public string $selectedPeriod = '12_months';

    public ?string $selectedIndustry = null;

    public ?string $selectedSize = null;

    #[Computed]
    public function stats(): array
    {
        // Use cached/approximate counts for demo performance
        return [
            'total_customers' => CustomerEnrichment::count(),
            'enriched_customers' => CustomerEnrichment::enriched()->count(),
            'pending_enrichment' => CustomerEnrichment::notEnriched()->count(),
            'failed_enrichment' => CustomerEnrichment::failed()->count(),
        ];
    }

    #[Computed]
    public function industryDistribution(): array
    {
        return CustomerEnrichment::query()
            ->enriched()
            ->whereNotNull('industry_sector')
            ->select('industry_sector', DB::raw('COUNT(*) as count'))
            ->groupBy('industry_sector')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->industry_sector,
                'value' => $row->count,
            ])
            ->toArray();
    }

    #[Computed]
    public function sizeDistribution(): array
    {
        $sizes = ['micro' => 0, 'small' => 0, 'medium' => 0, 'large' => 0];

        $data = CustomerEnrichment::query()
            ->enriched()
            ->whereNotNull('company_size')
            ->select('company_size', DB::raw('COUNT(*) as count'))
            ->groupBy('company_size')
            ->get()
            ->keyBy('company_size');

        foreach ($sizes as $size => $default) {
            $sizes[$size] = $data->get($size)?->count ?? 0;
        }

        return $sizes;
    }

    #[Computed]
    public function cityDistribution(): array
    {
        return CustomerEnrichment::query()
            ->enriched()
            ->whereNotNull('city')
            ->select('city', DB::raw('COUNT(*) as count'))
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->city,
                'value' => $row->count,
            ])
            ->toArray();
    }

    #[Computed]
    public function topCustomers(): array
    {
        // Get top customers by shipment count from legacy DB (last 6 months for demo)
        return Kuldemeny::query()
            ->notDeleted()
            ->where('k_kiszallitas_datum', '>=', now()->subDays(30))
            ->select('k_ugyfelkod', 'k_uf_ceg_nev', DB::raw('COUNT(*) as shipment_count'), DB::raw('SUM(k_fd_brutto) as total_revenue'))
            ->groupBy('k_ugyfelkod', 'k_uf_ceg_nev')
            ->orderByDesc('total_revenue')
            ->limit(1)
            ->get()
            ->map(function ($row) {
                // Skip enrichment lookup for demo performance
                $enrichment = null;

                return [
                    'ugyfelkod' => $row->k_ugyfelkod,
                    'company_name' => $row->k_uf_ceg_nev,
                    'shipment_count' => $row->shipment_count,
                    'total_revenue' => $row->total_revenue,
                    'industry' => $enrichment?->industry_sector,
                    'size' => $enrichment?->company_size,
                    'city' => $enrichment?->city,
                    'enriched' => $enrichment?->enriched_at !== null,
                ];
            })
            ->toArray();
    }

    #[Computed]
    public function customerAverages(): array
    {
        $customers = $this->topCustomers;

        $totalShipments = array_sum(array_column($customers, 'shipment_count'));
        $totalRevenue = array_sum(array_column($customers, 'total_revenue'));

        return [
            'total_shipments' => $totalShipments,
            'total_revenue' => $totalRevenue,
            'avg_per_package' => $totalShipments > 0 ? round($totalRevenue / $totalShipments) : 0,
        ];
    }

    #[Computed]
    public function industryChartOptions(): array
    {
        $data = $this->industryDistribution;

        if (empty($data)) {
            $data = [
                ['name' => 'Nincs adat', 'value' => 1],
            ];
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
                    'center' => ['65%', '50%'],
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
                    'data' => $data,
                ],
            ],
        ];
    }

    #[Computed]
    public function sizeChartOptions(): array
    {
        $sizes = $this->sizeDistribution;
        $labels = [
            'micro' => 'Mikro (<10)',
            'small' => 'Kis (10-49)',
            'medium' => 'Közepes (50-249)',
            'large' => 'Nagy (250+)',
        ];

        $data = [];
        foreach ($sizes as $key => $value) {
            $data[] = ['name' => $labels[$key], 'value' => $value];
        }

        if (array_sum($sizes) === 0) {
            $data = [['name' => 'Nincs adat', 'value' => 1]];
        }

        return [
            'tooltip' => [
                'trigger' => 'item',
                'formatter' => '{b}: {c} ({d}%)',
            ],
            'series' => [
                [
                    'type' => 'pie',
                    'radius' => '70%',
                    'center' => ['50%', '50%'],
                    'data' => $data,
                    'itemStyle' => [
                        'borderRadius' => 4,
                    ],
                    'label' => [
                        'show' => true,
                        'formatter' => '{b}: {c}',
                    ],
                ],
            ],
        ];
    }

    #[Computed]
    public function cityChartOptions(): array
    {
        $data = $this->cityDistribution;

        if (empty($data)) {
            $data = [['name' => 'Nincs adat', 'value' => 0]];
        }

        return [
            'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => ['type' => 'shadow'],
            ],
            'grid' => [
                'left' => 100,
                'right' => 30,
                'top' => 10,
                'bottom' => 30,
            ],
            'xAxis' => [
                'type' => 'value',
            ],
            'yAxis' => [
                'type' => 'category',
                'data' => array_reverse(array_column($data, 'name')),
                'axisLabel' => ['interval' => 0],
            ],
            'series' => [
                [
                    'type' => 'bar',
                    'data' => array_reverse(array_column($data, 'value')),
                    'itemStyle' => [
                        'color' => '#3b82f6',
                        'borderRadius' => [0, 4, 4, 0],
                    ],
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.insights.sender-insights');
    }
}
