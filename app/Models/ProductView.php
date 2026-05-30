<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    use HasUuids;

    protected $table = 'product_views';

    protected $guarded = [];

    protected $fillable = ['product_id', 'session_id', 'visitor_id', 'url', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];
}
