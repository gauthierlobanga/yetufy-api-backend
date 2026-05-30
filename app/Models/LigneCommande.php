<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LigneCommande extends Model
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
        'commande_id',
        'produit_id',
        'variante_produit_id',
        'quantite',
        'prix_unitaire',
        'prix_total',
        'taxe',
        'remise',
        'options',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'prix_unitaire' => 'decimal:2',
        'prix_total' => 'decimal:2',
        'taxe' => 'decimal:2',
        'remise' => 'decimal:2',
        'options' => 'array',
    ];

    // Relations
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProduit::class);
    }

    // Accessors
    public function getSousTotalAttribute(): float
    {
        return $this->prix_unitaire * $this->quantite;
    }

    public function getTotalTtcAttribute(): float
    {
        return $this->sous_total + ($this->taxe * $this->quantite) - $this->remise;
    }

    public function getNomProduitAttribute(): string
    {
        if ($this->variante) {
            return $this->produit->nom.' - '.$this->variante->nom.': '.$this->variante->valeur;
        }

        return $this->produit->nom;
    }
}
