<?php

// app/Models/ItemPanier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemPanier extends Model
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
        'produit_id',
        'variante_produit_id',
        'quantite',
        'prix_unitaire',
        'prix_total',
        'taxe_unitaire',
        'remise_unitaire',
        'options_selectionnees',
        'personnalisation',
        'added_at',
        'updated_at',
    ];

    protected $casts = [
        'options_selectionnees' => 'array',
        'personnalisation' => 'array',
        'quantite' => 'integer',
        'prix_unitaire' => 'decimal:2',
        'prix_total' => 'decimal:2',
        'taxe_unitaire' => 'decimal:2',
        'remise_unitaire' => 'decimal:2',
        'added_at' => 'datetime',
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProduit::class);
    }

    /**
     * Accessors
     */
    public function getNomProduitAttribute(): string
    {
        if ($this->variante) {
            return $this->produit->nom.' - '.$this->variante->nom.': '.$this->variante->valeur;
        }

        return $this->produit->nom;
    }

    public function getImageAttribute(): ?string
    {
        return $this->produit->image_principale_thumb;
    }
}
