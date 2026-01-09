<?php

namespace App\Console\Commands;

use App\Models\CustomerEnrichment;
use App\Models\Legacy\Ugyfel;
use App\Services\CustomerEnrichmentService;
use Illuminate\Console\Command;

class EnrichCustomers extends Command
{
    protected $signature = 'customers:enrich
        {--limit=100 : Maximum number of customers to process}
        {--force : Re-enrich already enriched customers}
        {--failed : Only retry failed enrichments}
        {--ugyfelkod= : Enrich a specific customer by code}';

    protected $description = 'Enrich customer data from public company registries';

    public function __construct(
        private CustomerEnrichmentService $enrichmentService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $failedOnly = $this->option('failed');
        $specificCode = $this->option('ugyfelkod');

        $this->info('Starting customer enrichment...');

        // Build query for customers to enrich
        $query = Ugyfel::query()
            ->notDeleted()
            ->whereNotNull('u_adoszam')
            ->where('u_adoszam', '!=', '');

        if ($specificCode) {
            $query->where('u_ugyfelkod', $specificCode);
        }

        $customers = $query->limit($limit)->get();

        if ($customers->isEmpty()) {
            $this->warn('No customers found to enrich.');

            return Command::SUCCESS;
        }

        $this->info("Found {$customers->count()} customers to process.");

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        $stats = [
            'enriched' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        foreach ($customers as $customer) {
            $bar->advance();

            // Check if already enriched
            $existing = CustomerEnrichment::where('ugyfelkod', $customer->u_ugyfelkod)->first();

            if ($existing && ! $force) {
                if ($failedOnly && ! $existing->enrichment_failed) {
                    $stats['skipped']++;

                    continue;
                }
                if (! $failedOnly && $existing->enriched_at) {
                    $stats['skipped']++;

                    continue;
                }
            }

            // Enrich customer
            $result = $this->enrichmentService->enrichByTaxNumber(
                $customer->u_ugyfelkod,
                $customer->u_adoszam,
                $customer->u_ceg_nev
            );

            if ($result && $result->enriched_at && ! $result->enrichment_failed) {
                $stats['enriched']++;
            } else {
                $stats['failed']++;
            }

            // Small delay to be respectful to external APIs
            usleep(500000); // 0.5 seconds
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Status', 'Count'],
            [
                ['Enriched', $stats['enriched']],
                ['Skipped', $stats['skipped']],
                ['Failed', $stats['failed']],
            ]
        );

        return Command::SUCCESS;
    }
}
