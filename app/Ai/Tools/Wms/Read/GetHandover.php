<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ShowTool;

class GetHandover extends ShowTool
{
    public function description(): string
    {
        return 'Ambil detail satu pemindahan barang berdasarkan id, lengkap dengan barang, rack, dan riwayatnya.';
    }

    protected function path(): string
    {
        return 'handovers';
    }

    public function permission(): ?string
    {
        return 'handover.read';
    }
}
