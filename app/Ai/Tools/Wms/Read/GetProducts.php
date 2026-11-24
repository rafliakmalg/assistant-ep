<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ListTool;

class GetProducts extends ListTool
{
    public function description(): string
    {
        return 'Cari daftar barang. Gunakan search untuk nama atau SKU. Stok barang ada di tool stok.';
    }

    protected function fields(): array
    {
        return ['active', 'base_unit_id', 'category_id', 'created_at', 'min_stock', 'name', 'sku'];
    }

    protected function path(): string
    {
        return 'products';
    }

    public function permission(): ?string
    {
        return 'product.read';
    }
}
