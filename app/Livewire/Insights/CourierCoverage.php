<?php

namespace App\Livewire\Insights;

use App\Models\Legacy\Futar;
use App\Models\Legacy\Futarkorok;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Futár Lefedettség')]
class CourierCoverage extends Component
{
    public ?string $selectedCourier = null;

    public ?string $searchPostalCode = null;

    public function getCoverageDataProperty(): array
    {
        $routes = Futarkorok::query()
            ->notDeleted()
            ->whereNotNull('fk_terulet_ki')
            ->where('fk_terulet_ki', '!=', '')
            ->get();

        $courierNames = Futar::query()
            ->notDeleted()
            ->pluck('f_nev', 'f_kod')
            ->toArray();

        $coverageData = [];
        $colors = $this->generateColors(count($routes));
        $colorIndex = 0;

        foreach ($routes as $route) {
            $courierCode = $route->fk_kod;
            $courierName = $courierNames[$courierCode] ?? null;
            $postalCodes = $this->parsePostalCodes($route->fk_terulet_ki);

            if (empty($postalCodes)) {
                continue;
            }

            $coverageData[] = [
                'courier_code' => $courierCode,
                'courier_name' => $courierName,
                'postal_codes' => $postalCodes,
                'color' => $colors[$colorIndex % count($colors)],
            ];
            $colorIndex++;
        }

        return $coverageData;
    }

    public function getPostalCodeLookupProperty(): array
    {
        $lookup = [];

        foreach ($this->coverageData as $courier) {
            foreach ($courier['postal_codes'] as $postalCode) {
                if (! isset($lookup[$postalCode])) {
                    $lookup[$postalCode] = [];
                }
                $lookup[$postalCode][] = [
                    'code' => $courier['courier_code'],
                    'name' => $courier['courier_name'],
                    'color' => $courier['color'],
                ];
            }
        }

        return $lookup;
    }

    public function selectCourier(?string $courierCode): void
    {
        $this->selectedCourier = $this->selectedCourier === $courierCode ? null : $courierCode;
    }

    public function searchPostalCodeCoverage(): array
    {
        if (empty($this->searchPostalCode)) {
            return [];
        }

        $search = trim($this->searchPostalCode);
        $results = [];

        foreach ($this->coverageData as $courier) {
            foreach ($courier['postal_codes'] as $postalCode) {
                if ($postalCode === $search || $this->matchesWildcard($search, $postalCode)) {
                    $results[] = [
                        'code' => $courier['courier_code'],
                        'name' => $courier['courier_name'],
                        'color' => $courier['color'],
                    ];
                    break;
                }
            }
        }

        return $results;
    }

    private function parsePostalCodes(string $postalCodeString): array
    {
        $codes = [];
        $parts = preg_split('/[\s,]+/', trim($postalCodeString));

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }

            // Handle wildcard patterns like "101X" or "25XX"
            if (str_contains($part, 'X')) {
                $codes = array_merge($codes, $this->expandWildcard($part));
            } else {
                $codes[] = $part;
            }
        }

        return array_values(array_unique($codes));
    }

    private function expandWildcard(string $pattern): array
    {
        $codes = [];
        $xCount = substr_count($pattern, 'X');

        if ($xCount === 1) {
            // Single X means 0-9
            for ($i = 0; $i <= 9; $i++) {
                $codes[] = str_replace('X', (string) $i, $pattern);
            }
        } elseif ($xCount === 2) {
            // XX means 00-99
            for ($i = 0; $i <= 99; $i++) {
                $codes[] = str_replace('XX', str_pad((string) $i, 2, '0', STR_PAD_LEFT), $pattern);
            }
        }

        return $codes;
    }

    private function matchesWildcard(string $search, string $pattern): bool
    {
        if (! str_contains($pattern, 'X')) {
            return $search === $pattern;
        }

        $regex = '/^' . str_replace('X', '\d', $pattern) . '$/';

        return (bool) preg_match($regex, $search);
    }

    private function generateColors(int $count): array
    {
        $baseColors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
            '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
            '#14b8a6', '#a855f7', '#22c55e', '#eab308', '#0ea5e9',
            '#d946ef', '#64748b', '#fb7185', '#4ade80', '#facc15',
            '#2563eb', '#dc2626', '#059669', '#d97706', '#7c3aed',
            '#db2777', '#0891b2', '#65a30d', '#ea580c', '#4f46e5',
        ];

        // If we need more colors, generate them
        while (count($baseColors) < $count) {
            $baseColors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        }

        return $baseColors;
    }

    public function render()
    {
        return view('livewire.insights.courier-coverage');
    }
}
