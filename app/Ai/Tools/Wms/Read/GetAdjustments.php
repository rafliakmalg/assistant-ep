<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ListTool;

class GetAdjustments extends ListTool
{
    public function description(): string
    {
        return 'Cari daftar penyesuaian stok (rusak, hilang, ditemukan, koreksi). Status: active atau cancelled.';
    }

    protected function fields(): array
    {
        return ['code', 'created_at', 'status'];
    }

    protected function path(): string
    {
        return 'adjustments';
    }

    public function permission(): ?string
    {
        return 'adjustment.read';
    }
}
