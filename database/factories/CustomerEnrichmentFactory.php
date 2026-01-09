<?php

namespace Database\Factories;

use App\Models\CustomerEnrichment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerEnrichment>
 */
class CustomerEnrichmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teaorCode = $this->faker->randomElement(['4520', '4941', '6201', '4711', '5610']);
        $employeeCount = $this->faker->numberBetween(1, 500);

        return [
            'ugyfelkod' => 'UGY'.$this->faker->unique()->numerify('######'),
            'company_name' => $this->faker->company(),
            'tax_number' => $this->faker->numerify('########-#-##'),
            'teaor_code' => $teaorCode,
            'teaor_name' => $this->faker->words(3, true),
            'industry_sector' => CustomerEnrichment::mapTeaorToSector($teaorCode),
            'employee_count' => $employeeCount,
            'company_size' => CustomerEnrichment::calculateCompanySize($employeeCount),
            'revenue_eur' => $this->faker->randomFloat(2, 100000, 10000000),
            'revenue_huf' => $this->faker->randomFloat(0, 40000000, 4000000000),
            'website' => $this->faker->url(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'enrichment_source' => 'factory',
            'enriched_at' => now(),
            'enrichment_failed' => false,
        ];
    }

    /**
     * State for enriched customers.
     */
    public function enriched(): static
    {
        return $this->state(fn (array $attributes) => [
            'enriched_at' => now(),
            'enrichment_failed' => false,
            'enrichment_error' => null,
        ]);
    }

    /**
     * State for not enriched customers.
     */
    public function notEnriched(): static
    {
        return $this->state(fn (array $attributes) => [
            'enriched_at' => null,
            'enrichment_failed' => false,
            'enrichment_error' => null,
            'industry_sector' => null,
            'company_size' => null,
        ]);
    }

    /**
     * State for failed enrichment.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrichment_failed' => true,
            'enrichment_error' => 'Failed to fetch company data',
        ]);
    }

    /**
     * State for micro company size.
     */
    public function micro(): static
    {
        $employeeCount = $this->faker->numberBetween(1, 9);

        return $this->state(fn (array $attributes) => [
            'employee_count' => $employeeCount,
            'company_size' => 'micro',
        ]);
    }

    /**
     * State for small company size.
     */
    public function small(): static
    {
        $employeeCount = $this->faker->numberBetween(10, 49);

        return $this->state(fn (array $attributes) => [
            'employee_count' => $employeeCount,
            'company_size' => 'small',
        ]);
    }

    /**
     * State for medium company size.
     */
    public function medium(): static
    {
        $employeeCount = $this->faker->numberBetween(50, 249);

        return $this->state(fn (array $attributes) => [
            'employee_count' => $employeeCount,
            'company_size' => 'medium',
        ]);
    }

    /**
     * State for large company size.
     */
    public function large(): static
    {
        $employeeCount = $this->faker->numberBetween(250, 1000);

        return $this->state(fn (array $attributes) => [
            'employee_count' => $employeeCount,
            'company_size' => 'large',
        ]);
    }
}
