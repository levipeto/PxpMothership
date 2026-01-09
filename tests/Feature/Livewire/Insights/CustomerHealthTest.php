<?php

use App\Livewire\Insights\CustomerHealth;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('CustomerHealth Page', function () {
    it('can render the customer health page', function () {
        $this->get(route('insights.customer-health'))
            ->assertOk()
            ->assertSeeLivewire(CustomerHealth::class);
    });

    it('requires authentication', function () {
        auth()->logout();

        $this->get(route('insights.customer-health'))
            ->assertRedirect(route('login'));
    });
});

describe('CustomerHealth Component', function () {
    it('renders the component successfully', function () {
        Livewire::test(CustomerHealth::class)
            ->assertOk()
            ->assertSee('Ügyfél Egészség');
    });

    it('shows health category labels', function () {
        Livewire::test(CustomerHealth::class)
            ->assertSee('Összes')
            ->assertSee('Egészséges')
            ->assertSee('Figyelem')
            ->assertSee('Veszély')
            ->assertSee('Kritikus');
    });

    it('shows table headers', function () {
        Livewire::test(CustomerHealth::class)
            ->assertSee('Státusz')
            ->assertSee('Cégnév')
            ->assertSee('Utolsó');
    });

    it('returns month labels for all 12 months', function () {
        $component = Livewire::test(CustomerHealth::class);

        $labels = $component->instance()->monthLabels;

        expect($labels)->toHaveKeys([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
    });

    it('shows chart section heading', function () {
        Livewire::test(CustomerHealth::class)
            ->assertSee('Egészség megoszlás');
    });

    it('shows at-risk customers section', function () {
        Livewire::test(CustomerHealth::class)
            ->assertSee('Figyelmet igénylő ügyfelek');
    });

    it('returns health stats as array with correct keys', function () {
        $component = Livewire::test(CustomerHealth::class);

        $stats = $component->instance()->healthStats;

        expect($stats)->toHaveKeys(['total', 'healthy', 'warning', 'at_risk', 'critical', 'churned']);
    });

    it('returns health chart options with correct structure', function () {
        $component = Livewire::test(CustomerHealth::class);

        $options = $component->instance()->healthChartOptions;

        expect($options)->toHaveKeys(['tooltip', 'legend', 'series']);
    });
});
