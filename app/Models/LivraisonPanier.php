<?php

// app/Models/LivraisonPanier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LivraisonPanier extends Model
{
    use HasFactory, SoftDeletes;
    use HasUuids;

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
        'panier_id',
        'adresse_id',
        'mode',
        'cout',
        'date_estimee',
        'options',
        'selected_at',
    ];

    protected $casts = [
        'options' => 'array',
        'cout' => 'decimal:2',
        'date_estimee' => 'datetime',
        'selected_at' => 'datetime',
    ];

    const MODE_DOMICILE = 'domicile';

    const MODE_POINT_RELAIS = 'point_relais';

    const MODE_EXPRESS = 'express';

    /**
     * Relations
     */
    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    public function adresse(): BelongsTo
    {
        return $this->belongsTo(Adresse::class);
    }

    /**
     * Accessors
     */
    public function getLibelleModeAttribute(): string
    {
        return match ($this->mode) {
            self::MODE_DOMICILE => 'Livraison à domicile',
            self::MODE_POINT_RELAIS => 'Point relais',
            self::MODE_EXPRESS => 'Livraison express',
            default => $this->mode,
        };
    }
}
