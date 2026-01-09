<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerEnrichment extends Model
{
    use HasFactory;
    protected $fillable = [
        'ugyfelkod',
        'company_name',
        'tax_number',
        'teaor_code',
        'teaor_name',
        'industry_sector',
        'employee_count',
        'company_size',
        'revenue_eur',
        'revenue_huf',
        'website',
        'email',
        'phone',
        'city',
        'postal_code',
        'address',
        'enrichment_data',
        'enrichment_source',
        'enriched_at',
        'enrichment_failed',
        'enrichment_error',
    ];

    protected function casts(): array
    {
        return [
            'enrichment_data' => 'array',
            'enriched_at' => 'datetime',
            'enrichment_failed' => 'boolean',
            'employee_count' => 'integer',
            'revenue_eur' => 'decimal:2',
            'revenue_huf' => 'decimal:0',
        ];
    }

    /**
     * Company size categories based on EU SME definitions.
     */
    public const SIZE_MICRO = 'micro';       // < 10 employees

    public const SIZE_SMALL = 'small';       // 10-49 employees

    public const SIZE_MEDIUM = 'medium';     // 50-249 employees

    public const SIZE_LARGE = 'large';       // 250+ employees

    /**
     * Determine company size from employee count.
     */
    public static function calculateCompanySize(?int $employees): ?string
    {
        if ($employees === null) {
            return null;
        }

        return match (true) {
            $employees < 10 => self::SIZE_MICRO,
            $employees < 50 => self::SIZE_SMALL,
            $employees < 250 => self::SIZE_MEDIUM,
            default => self::SIZE_LARGE,
        };
    }

    /**
     * Map TEÁOR code to broader industry sector.
     */
    public static function mapTeaorToSector(string $teaorCode): string
    {
        $prefix = substr($teaorCode, 0, 2);

        return match (true) {
            in_array($prefix, ['01', '02', '03']) => 'Agriculture',
            in_array($prefix, ['05', '06', '07', '08', '09']) => 'Mining',
            in_array($prefix, ['10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33']) => 'Manufacturing',
            in_array($prefix, ['35']) => 'Energy',
            in_array($prefix, ['36', '37', '38', '39']) => 'Utilities',
            in_array($prefix, ['41', '42', '43']) => 'Construction',
            in_array($prefix, ['45', '46', '47']) => 'Retail & Wholesale',
            in_array($prefix, ['49', '50', '51', '52', '53']) => 'Transportation & Logistics',
            in_array($prefix, ['55', '56']) => 'Hospitality',
            in_array($prefix, ['58', '59', '60', '61', '62', '63']) => 'IT & Media',
            in_array($prefix, ['64', '65', '66']) => 'Finance & Insurance',
            in_array($prefix, ['68']) => 'Real Estate',
            in_array($prefix, ['69', '70', '71', '72', '73', '74', '75']) => 'Professional Services',
            in_array($prefix, ['77', '78', '79', '80', '81', '82']) => 'Business Services',
            in_array($prefix, ['84']) => 'Public Administration',
            in_array($prefix, ['85']) => 'Education',
            in_array($prefix, ['86', '87', '88']) => 'Healthcare',
            in_array($prefix, ['90', '91', '92', '93']) => 'Arts & Recreation',
            in_array($prefix, ['94', '95', '96']) => 'Other Services',
            default => 'Other',
        };
    }

    public function scopeEnriched(Builder $query): Builder
    {
        return $query->whereNotNull('enriched_at')->where('enrichment_failed', false);
    }

    public function scopeNotEnriched(Builder $query): Builder
    {
        return $query->whereNull('enriched_at')->where('enrichment_failed', false);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('enrichment_failed', true);
    }

    public function scopeByIndustry(Builder $query, string $sector): Builder
    {
        return $query->where('industry_sector', $sector);
    }

    public function scopeBySize(Builder $query, string $size): Builder
    {
        return $query->where('company_size', $size);
    }

    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    public function markAsEnriched(string $source, array $data = []): void
    {
        $this->update([
            'enrichment_source' => $source,
            'enrichment_data' => $data,
            'enriched_at' => now(),
            'enrichment_failed' => false,
            'enrichment_error' => null,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'enrichment_failed' => true,
            'enrichment_error' => $error,
        ]);
    }
}
