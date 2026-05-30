<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entrepot extends Model
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
        'nom',
        'adresse',
        'telephone',
        'email',
        'est_principal',
        'configuration',
    ];

    protected $casts = [
        'est_principal' => 'boolean',
        'configuration' => 'array',
    ];

    /**
     * Relations
     */
    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'produit_entrepot')
            ->withPivot('quantite', 'quantite_reservee', 'updated_at')
            ->withTimestamps();
    }

    public function mouvementsStock(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }

    /**
     * Accessors
     */
    public function getStockTotalAttribute(): int
    {
        return $this->produits()->sum('produit_entrepot.quantite');
    }

    /**
     * Méthodes métier
     */
    public function getStockProduit(Produit $produit): int
    {
        $pivot = $this->produits()
            ->where('produit_id', $produit->id)
            ->first();

        return $pivot?->pivot->quantite ?? 0;
    }
}
