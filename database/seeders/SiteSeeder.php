<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::query()->updateOrCreate(
            ['code' => 'wms'],
            [
                'driver' => Site::DRIVER_WMS,
                'name' => 'Sun Logistic WMS',
                'url' => 'http://wms-ep.test/api',
            ],
        );
    }
}
