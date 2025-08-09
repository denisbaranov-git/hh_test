<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => 'string',
    ];
    public function scopeAllowed($query)
    {
        return $query->where('status', 'Allowed');
    }
}
