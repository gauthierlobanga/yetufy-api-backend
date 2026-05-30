<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Visitor extends Model
{
    use HasUuids;

    protected $table = 'visitors';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'session_id', 'ip_address', 'user_agent', 'device_type',
        'browser', 'platform', 'language', 'country_code', 'city',
        'is_authenticated', 'user_id', 'referrer',
        'first_visit_at', 'last_visit_at', 'total_visits',
    ];

    protected $casts = [
        'is_authenticated' => 'boolean',
        'first_visit_at' => 'datetime',
        'last_visit_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($visitor) {
            if (empty($visitor->id)) {
                $visitor->id = (string) Str::uuid();
            }
        });
    }

    public function pageViews()
    {
        return $this->hasMany(PageView::class);
    }

    public function events()
    {
        return $this->hasMany(VisitorEvent::class);
    }

    // Dernière page vue
    public function lastPageView()
    {
        return $this->hasOne(PageView::class)->latest('viewed_at');
    }

    // Scope pour les visiteurs actifs (dernières 5 minutes)
    public function scopeActive($query, $minutes = 5)
    {
        return $query->where('last_visit_at', '>=', now()->subMinutes($minutes));
    }
}
