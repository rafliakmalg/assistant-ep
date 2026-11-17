<?php

namespace App\Ai\Tools\Wms\Write;

use App\Ai\Tools\Wms\Base\ActionTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateMovement extends ActionTool
{
    public function description(): string
    {
        return 'Catat pergerakan stok langsung (tanpa dokumen pemindahan): rack_to_id saja untuk barang masuk, rack_from_id saja untuk barang keluar. JANGAN dipakai untuk memindahkan barang antar rack; untuk itu gunakan tool pembuat pemindahan. Hanya pakai bila pengguna jelas meminta mencatat barang masuk atau keluar. Perlu persetujuan pengguna.';
    }

    protected function fields(JsonSchema $schema): array
    {
        return [
            'note' => $schema->string(),
            'product_id' => $schema->integer()->required(),
            'quantity' => $schema->number()->required(),
            'rack_from_id' => $schema->integer(),
            'rack_to_id' => $schema->integer(),
        ];
    }

    protected function path(Request $request): string
    {
        return 'movements';
    }
}
