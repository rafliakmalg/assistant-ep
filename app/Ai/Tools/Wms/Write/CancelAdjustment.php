<?php

namespace App\Ai\Tools\Wms\Write;

use App\Ai\Tools\Wms\Base\ActionTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CancelAdjustment extends ActionTool
{
    public function description(): string
    {
        return 'Batalkan penyesuaian stok. Stok dikembalikan seperti sebelum penyesuaian. Perlu persetujuan pengguna.';
    }

    protected function fields(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
        ];
    }

    protected function path(Request $request): string
    {
        return 'adjustments/'.$request['id'].'/cancel';
    }

    public function permission(): ?string
    {
        return 'adjustment.cancel';
    }
}
