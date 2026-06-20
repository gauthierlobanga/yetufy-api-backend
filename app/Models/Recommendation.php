<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'source',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
    ];

    // Relation vers l'utilisateur local (TenantUser)
    public function user(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class);
    }

    // Scopes utiles
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNotExpired($query)
    {
        return $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
