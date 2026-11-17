<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ListTool;

class GetInventories extends ListTool
{
    public function description(): string
    {
        return 'Cari stok barang per rack. quantity adalah stok tersedia, quantity_incoming adalah barang yang sedang menuju rack.';
    }

    protected function fields(): array
    {
        return ['created_at', 'product_id', 'product.sku', 'quantity', 'quantity_incoming', 'rack.code', 'rack.warehouse.branch_id', 'rack.warehouse_id', 'rack_id'];
    }

    protected function path(): string
    {
        return 'inventories';
    }
}
