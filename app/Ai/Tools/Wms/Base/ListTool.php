<?php

namespace App\Ai\Tools\Wms\Base;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

abstract class ListTool extends WmsTool
{
    abstract protected function fields(): array;

    public function handle(Request $request): Stringable|string
    {
        $query = [
            'filters' => $request['filters'] ?? [],
            'page' => $request['page'] ?? 1,
            'per_page' => 10,
            'search' => $request['search'] ?? null,
        ];

        return $this->client->get($this->path(), $query);
    }

    abstract protected function path(): string;

    public function schema(JsonSchema $schema): array
    {
        $operators = ['between', 'eq', 'gt', 'gte', 'in', 'like', 'lt', 'lte', 'ne', 'null'];

        $filter = $schema->object(fn (JsonSchema $item) => [
            'field' => $item->string()->enum($this->fields())->required(),
            'operator' => $item->string()->enum($operators)->required(),
            'value' => $item->string()->required(),
        ]);

        return [
            'filters' => $schema->array()->items($filter),
            'page' => $schema->integer()->min(1),
            'search' => $schema->string(),
        ];
    }
}
