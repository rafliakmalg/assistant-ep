<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\SiteUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteUser>
 */
class SiteUserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'external_id' => (string) fake()->unique()->numberBetween(1, 100000),
            'name' => fake()->name(),
            'role' => 'operator',
        ];
    }
}
