<?php

// app/Models/LigneCommandeAchat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LigneCommandeAchat extends Model
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
        'commande_achat',
        'produit_id',
        'quantite',
        'quantite_recue',
        'prix_unitaire_ht',
        'total_ht',
        'tva',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'prix_unitaire_ht' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'tva' => 'decimal:2',
    ];

    public function commandeAchat(): BelongsTo
    {
        return $this->belongsTo(CommandeAchat::class, 'commande_achat');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function getQuantiteRestanteAttribute(): int
    {
        return max(0, $this->quantite - $this->quantite_recue);
    }

    public function getEstTotalementRecueAttribute(): bool
    {
        return $this->quantite_recue >= $this->quantite;
    }
}
