<?php

namespace App\Services;

use App\Models\CustomerEnrichment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerEnrichmentService
{
    private const CEGINFORMACIO_BASE_URL = 'https://www.ceginformacio.hu';

    /**
     * Enrich a customer by tax number.
     */
    public function enrichByTaxNumber(string $ugyfelkod, string $taxNumber, ?string $companyName = null): ?CustomerEnrichment
    {
        $enrichment = CustomerEnrichment::firstOrCreate(
            ['ugyfelkod' => $ugyfelkod],
            ['company_name' => $companyName, 'tax_number' => $taxNumber]
        );

        if ($enrichment->enriched_at && ! $enrichment->enrichment_failed) {
            return $enrichment;
        }

        try {
            $data = $this->fetchCompanyData($taxNumber, $companyName);

            if ($data) {
                $this->updateEnrichment($enrichment, $data);

                return $enrichment->fresh();
            }

            $enrichment->markAsFailed('No data found');

            return $enrichment;
        } catch (\Exception $e) {
            Log::error('Customer enrichment failed', [
                'ugyfelkod' => $ugyfelkod,
                'tax_number' => $taxNumber,
                'error' => $e->getMessage(),
            ]);
            $enrichment->markAsFailed($e->getMessage());

            return $enrichment;
        }
    }

    /**
     * Fetch company data from ceginformacio.hu or web search.
     *
     * @return array<string, mixed>|null
     */
    private function fetchCompanyData(string $taxNumber, ?string $companyName): ?array
    {
        // Clean tax number (remove dashes, spaces)
        $cleanTaxNumber = preg_replace('/[^0-9]/', '', $taxNumber);

        // Try to search on ceginformacio.hu
        $searchUrl = self::CEGINFORMACIO_BASE_URL.'/kereses?search='.urlencode($cleanTaxNumber);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; CompanyEnrichment/1.0)',
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'hu-HU,hu;q=0.9,en;q=0.8',
                ])
                ->get($searchUrl);

            if ($response->successful()) {
                return $this->parseCompanyPage($response->body(), $cleanTaxNumber, $companyName);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch from ceginformacio.hu', [
                'tax_number' => $taxNumber,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Parse company data from HTML response.
     *
     * @return array<string, mixed>|null
     */
    private function parseCompanyPage(string $html, string $taxNumber, ?string $companyName): ?array
    {
        $data = [
            'source' => 'ceginformacio',
            'tax_number' => $taxNumber,
            'raw_html_length' => strlen($html),
        ];

        // Extract TEÁOR code
        if (preg_match('/TEÁOR[:\s]+(\d{4}(?:\.\d{2})?)/i', $html, $matches)) {
            $data['teaor_code'] = $matches[1];
        }

        // Extract employee count
        if (preg_match('/(\d+)\s*(?:alkalmazott|fő|employee)/i', $html, $matches)) {
            $data['employee_count'] = (int) $matches[1];
        }

        // Extract revenue (HUF)
        if (preg_match('/(?:Árbevétel|Revenue)[:\s]*([0-9\s,\.]+)\s*(?:HUF|Ft|forint)/i', $html, $matches)) {
            $revenue = preg_replace('/[^0-9]/', '', $matches[1]);
            $data['revenue_huf'] = (int) $revenue;
        }

        // Extract address
        if (preg_match('/(?:Székhely|Address)[:\s]*([^<\n]+)/i', $html, $matches)) {
            $address = trim(strip_tags($matches[1]));
            $data['address'] = $address;

            // Try to extract city and postal code from address
            if (preg_match('/(\d{4})\s+([^,]+)/', $address, $addrMatches)) {
                $data['postal_code'] = $addrMatches[1];
                $data['city'] = trim($addrMatches[2]);
            }
        }

        // Extract website
        if (preg_match('/(?:Website|Weboldal|www)[:\s]*([a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,})/i', $html, $matches)) {
            $data['website'] = 'https://'.$matches[1];
        }

        // Extract company name if not provided
        if (! $companyName && preg_match('/<h1[^>]*>([^<]+)</i', $html, $matches)) {
            $data['company_name'] = trim($matches[1]);
        }

        // Only return if we got some useful data
        if (isset($data['teaor_code']) || isset($data['employee_count']) || isset($data['address'])) {
            return $data;
        }

        return null;
    }

    /**
     * Update enrichment record with fetched data.
     *
     * @param  array<string, mixed>  $data
     */
    private function updateEnrichment(CustomerEnrichment $enrichment, array $data): void
    {
        $updateData = [
            'enrichment_source' => $data['source'] ?? 'unknown',
            'enrichment_data' => $data,
            'enriched_at' => now(),
            'enrichment_failed' => false,
            'enrichment_error' => null,
        ];

        if (isset($data['teaor_code'])) {
            $updateData['teaor_code'] = $data['teaor_code'];
            $updateData['industry_sector'] = CustomerEnrichment::mapTeaorToSector($data['teaor_code']);
        }

        if (isset($data['employee_count'])) {
            $updateData['employee_count'] = $data['employee_count'];
            $updateData['company_size'] = CustomerEnrichment::calculateCompanySize($data['employee_count']);
        }

        if (isset($data['revenue_huf'])) {
            $updateData['revenue_huf'] = $data['revenue_huf'];
            $updateData['revenue_eur'] = $data['revenue_huf'] / 400; // Approximate conversion
        }

        if (isset($data['address'])) {
            $updateData['address'] = $data['address'];
        }

        if (isset($data['city'])) {
            $updateData['city'] = $data['city'];
        }

        if (isset($data['postal_code'])) {
            $updateData['postal_code'] = $data['postal_code'];
        }

        if (isset($data['website'])) {
            $updateData['website'] = $data['website'];
        }

        if (isset($data['company_name']) && ! $enrichment->company_name) {
            $updateData['company_name'] = $data['company_name'];
        }

        $enrichment->update($updateData);
    }

    /**
     * Create a manual enrichment entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function createManualEnrichment(string $ugyfelkod, array $data): CustomerEnrichment
    {
        $enrichment = CustomerEnrichment::updateOrCreate(
            ['ugyfelkod' => $ugyfelkod],
            array_merge($data, [
                'enrichment_source' => 'manual',
                'enriched_at' => now(),
                'enrichment_failed' => false,
            ])
        );

        // Calculate derived fields
        if (isset($data['teaor_code']) && ! isset($data['industry_sector'])) {
            $enrichment->industry_sector = CustomerEnrichment::mapTeaorToSector($data['teaor_code']);
        }

        if (isset($data['employee_count']) && ! isset($data['company_size'])) {
            $enrichment->company_size = CustomerEnrichment::calculateCompanySize($data['employee_count']);
        }

        $enrichment->save();

        return $enrichment;
    }
}
