<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ListTool;

class GetRacks extends ListTool
{
    public function description(): string
    {
        return 'Cari daftar rack. Dipakai untuk mencari id rack dari kode atau gudang.';
    }

    protected function fields(): array
    {
        return ['active', 'code', 'created_at', 'name', 'warehouse_id'];
    }

    protected function path(): string
    {
        return 'racks';
    }

    public function permission(): ?string
    {
        return 'rack.read';
    }
}
