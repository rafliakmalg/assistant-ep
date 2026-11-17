<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ListTool;

class GetHandovers extends ListTool
{
    public function description(): string
    {
        return 'Cari daftar pemindahan barang. Status: taken (sedang berjalan), completed, cancelled. custody_id adalah id pemegang barang.';
    }

    protected function fields(): array
    {
        return ['code', 'created_at', 'custody_id', 'status'];
    }

    protected function path(): string
    {
        return 'handovers';
    }
}
