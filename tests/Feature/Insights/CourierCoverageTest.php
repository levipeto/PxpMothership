<?php

use App\Livewire\Insights\CourierCoverage;
use App\Models\User;
use Livewire\Livewire;

test('courier coverage page requires authentication', function () {
    $this->get('/insights/courier-coverage')
        ->assertRedirect('/login');
});

test('authenticated user can view courier coverage page', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/insights/courier-coverage')
        ->assertOk();
});

test('courier coverage component renders successfully', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierCoverage::class)
        ->assertOk()
        ->assertSee('Futár Lefedettség')
        ->assertSee('Irányítószám keresése');
});

test('courier coverage displays courier list', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierCoverage::class)
        ->assertSee('Futárok')
        ->assertSee('Jelmagyarázat');
});

test('courier coverage can select a courier', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierCoverage::class)
        ->assertSet('selectedCourier', null)
        ->call('selectCourier', 'B01')
        ->assertSet('selectedCourier', 'B01')
        ->call('selectCourier', 'B01')
        ->assertSet('selectedCourier', null);
});

test('courier coverage can search postal codes', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierCoverage::class)
        ->set('searchPostalCode', '1031')
        ->assertSee('Keresési eredmények');
});
