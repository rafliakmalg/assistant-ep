<?php

namespace App\Contracts;

interface SiteClient
{
    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $path, array $query = []): string;

    /**
     * @param  array<string, mixed>  $data
     */
    public function post(string $path, array $data = []): string;
}
