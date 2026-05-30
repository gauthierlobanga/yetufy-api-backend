<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'wishlist_items';

    /**
     * Indique que les clés primaires sont de type string (UUID)
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indique que les clés primaires ne sont pas auto-incrémentées
     *
     * @var bool
     */
    public $incrementing = false;

    protected $fillable = [
        'wishlist_id',
        'produit_id',
        'quantite',
        // 'note',
        'added_at',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'added_at' => 'datetime',
    ];

    public $timestamps = false;

    // Relations
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    // Accessors
    public function getPrixTotalAttribute(): float
    {
        return ($this->produit?->prix_ttc ?? 0) * $this->quantite;
    }

    public function getFormattedPrixTotalAttribute(): string
    {
        return number_format($this->prix_total, 2).' €';
    }
}
