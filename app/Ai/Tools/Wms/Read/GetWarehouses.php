<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ListTool;

class GetWarehouses extends ListTool
{
    public function description(): string
    {
        return 'Cari daftar gudang. Dipakai untuk mencari id gudang dari nama atau kode.';
    }

    protected function fields(): array
    {
        return ['active', 'branch_id', 'code', 'created_at', 'name'];
    }

    protected function path(): string
    {
        return 'warehouses';
    }
}
