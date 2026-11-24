<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'wms',
            'driver' => Site::DRIVER_WMS,
            'name' => 'WMS',
            'url' => 'http://wms.test/api',
        ];
    }
}
