<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ShowTool;

class GetAdjustment extends ShowTool
{
    public function description(): string
    {
        return 'Ambil detail satu penyesuaian stok berdasarkan id, lengkap dengan barang dan alasannya.';
    }

    protected function path(): string
    {
        return 'adjustments';
    }
}
