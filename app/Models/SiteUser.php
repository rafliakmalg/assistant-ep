<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['external_id', 'name', 'role', 'site_id'])]
class SiteUser extends Model
{
    use HasFactory;

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
