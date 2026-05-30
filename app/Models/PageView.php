<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PageView extends Model
{
    use HasUuids;

    protected $table = 'page_views';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'visitor_id', 'url', 'route_name', 'page_title',
        'http_method', 'response_status', 'duration_ms', 'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($view) {
            if (empty($view->id)) {
                $view->id = (string) Str::uuid();
            }
            if (empty($view->viewed_at)) {
                $view->viewed_at = now();
            }
        });
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
