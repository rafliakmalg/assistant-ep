<?php

namespace App\Ai\Tools\Wms\Write;

use App\Ai\Tools\Wms\Base\ActionTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class TransferHandover extends ActionTool
{
    public function description(): string
    {
        return 'Serahkan pemegang pemindahan barang (Serah Barang) ke pengguna lain tanpa mengubah stok. custody_to_id adalah id pengguna tujuan, cari dengan tool pengguna. Perlu persetujuan pengguna.';
    }

    protected function fields(JsonSchema $schema): array
    {
        return [
            'custody_to_id' => $schema->integer()->required(),
            'id' => $schema->integer()->required(),
            'note' => $schema->string(),
        ];
    }

    protected function path(Request $request): string
    {
        return 'handovers/'.$request['id'].'/transfer';
    }

    public function permission(): ?string
    {
        return 'handover.transfer';
    }
}
