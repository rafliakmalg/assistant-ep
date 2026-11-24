<?php

namespace App\Ai\Tools\Wms\Read;

use App\Ai\Tools\Wms\Base\ListTool;

class GetUsers extends ListTool
{
    public function description(): string
    {
        return 'Cari daftar pengguna. Dipakai untuk mencari id pengguna dari nama.';
    }

    protected function fields(): array
    {
        return ['active', 'created_at', 'name'];
    }

    protected function path(): string
    {
        return 'users';
    }

    public function permission(): ?string
    {
        return null;
    }
}
