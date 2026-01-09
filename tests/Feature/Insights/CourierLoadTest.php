<?php

use App\Livewire\Insights\CourierLoad;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('courier load page requires authentication', function () {
    $this->get('/insights/courier-load')
        ->assertRedirect('/login');
});

test('authenticated user can view courier load page', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/insights/courier-load')
        ->assertOk();
});

test('courier load component renders successfully', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierLoad::class)
        ->assertOk()
        ->assertSee('Futár Terhelés')
        ->assertSee('Aktív Futárok')
        ->assertSee('Mai Rendelések');
});

test('courier load displays summary cards', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierLoad::class)
        ->assertSee('Aktív Futárok')
        ->assertSee('Mai Rendelések')
        ->assertSee('Teljesítve')
        ->assertSee('Függőben');
});

test('courier load displays weekly trend chart', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierLoad::class)
        ->assertSee('Heti Trend')
        ->assertSee('kattints egy hétre a részletekért');
});

test('courier load can select a week', function () {
    $this->actingAs(User::factory()->create());

    $lastWeekStart = Carbon::now()->subWeek()->startOfWeek()->toDateString();

    Livewire::test(CourierLoad::class)
        ->call('selectWeek', $lastWeekStart)
        ->assertSet('selectedWeekStart', $lastWeekStart);
});

test('courier load can reset to current week', function () {
    $this->actingAs(User::factory()->create());

    $currentWeekStart = Carbon::now()->startOfWeek()->toDateString();
    $lastWeekStart = Carbon::now()->subWeek()->startOfWeek()->toDateString();

    Livewire::test(CourierLoad::class)
        ->call('selectWeek', $lastWeekStart)
        ->assertSet('selectedWeekStart', $lastWeekStart)
        ->call('resetToCurrentWeek')
        ->assertSet('selectedWeekStart', $currentWeekStart);
});

test('courier load can toggle courier groups for yearly comparison', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierLoad::class)
        ->assertSet('selectedGroups', [])
        ->call('toggleGroup', 'P')
        ->assertSet('selectedGroups', ['P'])
        ->call('toggleGroup', 'B')
        ->assertSet('selectedGroups', ['P', 'B'])
        ->call('toggleGroup', 'P')
        ->assertSet('selectedGroups', ['B']);
});

test('courier load displays yearly comparison chart', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierLoad::class)
        ->assertSee('Éves Összehasonlítás')
        ->assertSee('Havi bontás futáronként')
        ->assertSee('Csoportok');
});

test('courier load initializes with current year for comparison', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CourierLoad::class)
        ->assertSet('comparisonYear', Carbon::now()->year);
});

test('courier load can switch comparison year', function () {
    $this->actingAs(User::factory()->create());

    $currentYear = Carbon::now()->year;
    $previousYear = $currentYear - 1;

    Livewire::test(CourierLoad::class)
        ->assertSet('comparisonYear', $currentYear)
        ->call('setComparisonYear', $previousYear)
        ->assertSet('comparisonYear', $previousYear);
});
