<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VisitorEvent extends Model
{
    use HasUuids;

    protected $fillable = ['session_id', 'visitor_id', 'event_type', 'url', 'product_id', 'order_id', 'value', 'metadata', 'occurred_at'];

    protected $casts = ['metadata' => 'array', 'occurred_at' => 'datetime'];
}
