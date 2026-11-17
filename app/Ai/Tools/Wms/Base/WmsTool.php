<?php

namespace App\Ai\Tools\Wms\Base;

use App\Services\WmsClient;
use Laravel\Ai\Contracts\Tool;

abstract class WmsTool implements Tool
{
    public function __construct(protected WmsClient $client) {}
}
