<?php

namespace App\Ai\Tools\Wms\Base;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

abstract class ShowTool extends WmsTool
{
    public function handle(Request $request): Stringable|string
    {
        return $this->client->get($this->path().'/'.$request['id']);
    }

    abstract protected function path(): string;

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
        ];
    }
}
