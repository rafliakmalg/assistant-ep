<?php

namespace App\Contracts;

use App\Models\Site;

interface SiteClient
{
    /**
     * @return array{id: int|string, name: string, role: string|null}|null
     */
    public function user(Site $site): ?array;
}
