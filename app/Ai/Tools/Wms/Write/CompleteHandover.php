<?php

namespace App\Ai\Tools\Wms\Write;

use App\Ai\Tools\Wms\Base\ActionTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CompleteHandover extends ActionTool
{
    public function description(): string
    {
        return 'Set selesai pemindahan barang yang sedang berjalan (status taken). Semua barang masuk ke rack tujuan. Perlu persetujuan pengguna.';
    }

    protected function fields(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
            'note' => $schema->string(),
        ];
    }

    protected function path(Request $request): string
    {
        return 'handovers/'.$request['id'].'/complete';
    }
}
