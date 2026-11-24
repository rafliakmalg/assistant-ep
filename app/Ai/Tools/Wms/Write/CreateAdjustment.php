<?php

namespace App\Ai\Tools\Wms\Write;

use App\Ai\Tools\Wms\Base\ActionTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateAdjustment extends ActionTool
{
    public function description(): string
    {
        return 'Buat penyesuaian stok (damaged rusak, lost hilang, found ditemukan, correction koreksi). quantity bertanda: negatif mengurangi stok, positif menambah. Cari id barang dan rack dengan tool lain. Perlu persetujuan pengguna.';
    }

    protected function fields(JsonSchema $schema): array
    {
        $item = $schema->object(fn (JsonSchema $item) => [
            'product_id' => $item->integer()->required(),
            'quantity' => $item->number()->required(),
            'rack_id' => $item->integer()->required(),
            'reason' => $item->string()->enum(['correction', 'damaged', 'found', 'lost'])->required(),
        ]);

        return [
            'items' => $schema->array()->items($item)->required(),
            'note' => $schema->string(),
        ];
    }

    protected function path(Request $request): string
    {
        return 'adjustments';
    }

    public function permission(): ?string
    {
        return 'adjustment.create';
    }
}
