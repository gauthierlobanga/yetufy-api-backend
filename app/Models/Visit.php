<?php

// app/Models/Visit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Visit extends Model
{
    use HasUuids;

    protected $table = 'visits';

    protected $guarded = [];

    protected $casts = [
        'utm_params' => 'array',
        'visited_at' => 'datetime',
        'duration' => 'integer',
    ];

    public function visitable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeForTenant($query, $tenant)
    {
        return $query->where('visitable_type', get_class($tenant))
            ->where('visitable_id', $tenant->id);
    }

    public function scopeForCentral($query)
    {
        return $query->whereNull('visitable_type')->whereNull('visitable_id');
    }
}
