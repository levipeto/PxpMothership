<?php

use App\Livewire\Insights\SenderInsights;
use App\Models\CustomerEnrichment;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('SenderInsights Page', function () {
    it('can render the sender insights page', function () {
        $this->get(route('insights.sender'))
            ->assertOk()
            ->assertSeeLivewire(SenderInsights::class);
    });

    it('requires authentication', function () {
        auth()->logout();

        $this->get(route('insights.sender'))
            ->assertRedirect(route('login'));
    });
});

describe('SenderInsights Component', function () {
    it('renders the component successfully', function () {
        Livewire::test(SenderInsights::class)
            ->assertOk()
            ->assertSee('Feladó Elemzés');
    });

    it('shows enrichment statistics labels', function () {
        Livewire::test(SenderInsights::class)
            ->assertSee('Összes ügyfél')
            ->assertSee('Feldolgozott')
            ->assertSee('Függőben')
            ->assertSee('Sikertelen');
    });

    it('shows chart section headings', function () {
        Livewire::test(SenderInsights::class)
            ->assertSee('Iparági megoszlás')
            ->assertSee('Cégméret megoszlás')
            ->assertSee('Top 10 város');
    });

    it('shows table headers', function () {
        Livewire::test(SenderInsights::class)
            ->assertSee('Cégnév')
            ->assertSee('Küldemények')
            ->assertSee('Bevétel')
            ->assertSee('Iparág')
            ->assertSee('Méret')
            ->assertSee('Város')
            ->assertSee('Státusz');
    });

    it('shows table structure regardless of data', function () {
        // Note: Legacy database (Kuldemeny) isn't refreshed by RefreshDatabase trait
        // So we just verify the table structure exists
        Livewire::test(SenderInsights::class)
            ->assertSee('Top 200 ügyfél');
    });

    it('displays enriched customer counts correctly', function () {
        CustomerEnrichment::factory()->enriched()->count(5)->create();
        CustomerEnrichment::factory()->notEnriched()->count(3)->create();
        CustomerEnrichment::factory()->failed()->count(2)->create();

        Livewire::test(SenderInsights::class)
            ->assertSee('5') // enriched
            ->assertSee('3') // pending
            ->assertSee('2'); // failed
    });

    it('includes industry sector in chart options', function () {
        CustomerEnrichment::factory()->enriched()->create([
            'industry_sector' => 'IT & Media',
        ]);

        $component = Livewire::test(SenderInsights::class);

        // Check that industry appears in the chart options data
        $industryData = $component->instance()->industryDistribution;

        expect($industryData)->toContain(['name' => 'IT & Media', 'value' => 1]);
    });

    it('includes company size labels in chart options', function () {
        CustomerEnrichment::factory()->micro()->enriched()->create();
        CustomerEnrichment::factory()->small()->enriched()->create();
        CustomerEnrichment::factory()->medium()->enriched()->create();
        CustomerEnrichment::factory()->large()->enriched()->create();

        $component = Livewire::test(SenderInsights::class);

        $sizeData = $component->instance()->sizeDistribution;

        expect($sizeData)->toBe([
            'micro' => 1,
            'small' => 1,
            'medium' => 1,
            'large' => 1,
        ]);
    });

    it('displays city names from enriched customers', function () {
        CustomerEnrichment::factory()->enriched()->create(['city' => 'Budapest']);
        CustomerEnrichment::factory()->enriched()->create(['city' => 'Debrecen']);

        Livewire::test(SenderInsights::class)
            ->assertSee('Budapest')
            ->assertSee('Debrecen');
    });

    it('calculates customer averages as numeric values', function () {
        $component = Livewire::test(SenderInsights::class);

        $averages = $component->instance()->customerAverages;

        // Verify averages are numeric (may have data from legacy DB)
        expect($averages)->toHaveKeys(['total_shipments', 'total_revenue', 'avg_per_package'])
            ->and($averages['total_shipments'])->toBeNumeric()
            ->and($averages['total_revenue'])->toBeNumeric()
            ->and($averages['avg_per_package'])->toBeNumeric();
    });

    it('shows totals row label in component', function () {
        // Note: Legacy database has data, so the totals row should show
        Livewire::test(SenderInsights::class)
            ->assertSee('Összesen (Top 200)');
    });
});
