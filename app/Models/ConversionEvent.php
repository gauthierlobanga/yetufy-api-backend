<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConversionEvent extends Model
{
    use HasUuids;

    protected $table = 'conversion_events';

    protected $guarded = [];

    protected $casts = ['completed_at' => 'datetime'];
}
