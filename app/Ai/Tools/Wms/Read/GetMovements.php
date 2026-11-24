<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ListTool;

class GetMovements extends ListTool
{
    public function description(): string
    {
        return 'Cari riwayat pergerakan stok (masuk, keluar, pindah).';
    }

    protected function fields(): array
    {
        return ['adjustment.code', 'created_at', 'handover.code', 'product.sku', 'product_id', 'quantity', 'rack_from.code', 'rack_from.warehouse_id', 'rack_to.code', 'rack_to.warehouse_id'];
    }

    protected function path(): string
    {
        return 'movements';
    }

    public function permission(): ?string
    {
        return 'movement.read';
    }
}
