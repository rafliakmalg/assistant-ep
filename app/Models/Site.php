<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['active', 'code', 'driver', 'identity', 'name', 'url'])]
class Site extends Model
{
    use HasFactory;

    public const DRIVER_WMS = 'wms';

    protected $attributes = [
        'active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'identity' => 'array',
        ];
    }

    public static function wmsIdentity(): array
    {
        return [
            'endpoint' => 'auth/me',
            'fields' => [
                'id' => 'data.id',
                'name' => 'data.name',
                'role' => 'data.role.name',
            ],
        ];
    }
}
