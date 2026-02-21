<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = ['path', 'type', 'imageable_type', 'imageable_id', 'sort_order'];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
