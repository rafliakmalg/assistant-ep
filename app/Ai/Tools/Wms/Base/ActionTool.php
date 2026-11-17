<?php

namespace App\Ai\Tools\Wms\Base;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Tools\Request;
use Stringable;

abstract class ActionTool extends WmsTool implements Approvable
{
    use InteractsWithApprovals;

    protected function body(Request $request): array
    {
        return Arr::except($request->all(), ['id', 'ringkasan']);
    }

    abstract protected function fields(JsonSchema $schema): array;

    public function handle(Request $request): Stringable|string
    {
        return $this->client->post($this->path($request), $this->body($request));
    }

    protected function needsApproval(Request $request): Approval|bool
    {
        return Approval::required($request['ringkasan'] ?? null);
    }

    abstract protected function path(Request $request): string;

    public function schema(JsonSchema $schema): array
    {
        $summary = $schema->string()
            ->description('Ringkasan aksi dalam satu kalimat bahasa Indonesia untuk dikonfirmasi pengguna. Sebut nama barang, nama rack atau gudang, dan jumlah. Jangan menyebut id.')
            ->required();

        return [
            ...$this->fields($schema),
            'ringkasan' => $summary,
        ];
    }
}
