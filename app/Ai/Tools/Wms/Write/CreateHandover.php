<?php

namespace App\Ai\Tools\Wms\Write;

use App\Ai\Tools\Wms\Base\ActionTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateHandover extends ActionTool
{
    public function description(): string
    {
        return 'Buat pemindahan barang (Ambil Barang). Stok langsung keluar dari rack asal dan menjadi barang menuju rack tujuan. Cari id barang dan rack dengan tool lain. Perlu persetujuan pengguna.';
    }

    protected function fields(JsonSchema $schema): array
    {
        $item = $schema->object(fn (JsonSchema $item) => [
            'product_id' => $item->integer()->required(),
            'quantity' => $item->number()->required(),
            'rack_from_id' => $item->integer()->required(),
            'rack_to_id' => $item->integer()->required(),
        ]);

        return [
            'items' => $schema->array()->items($item)->required(),
            'note' => $schema->string(),
        ];
    }

    protected function path(Request $request): string
    {
        return 'handovers';
    }
}
