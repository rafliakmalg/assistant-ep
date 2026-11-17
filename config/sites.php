<?php

use App\Ai\Agents\WmsAssistant;
use App\Services\WmsClient;

return [
    'drivers' => [
        'wms' => [
            'agent' => WmsAssistant::class,
            'client' => WmsClient::class,
        ],
    ],
];
