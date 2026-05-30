<?php

// app/Models/Fournisseur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fournisseur extends Model
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
        'contact',
        'email',
        'telephone',
        'adresse',
        'siret',
        'code_tva',
        'conditions',
        'coordonnees_bancaires',
        'est_actif',
        'delai_livraison_jours',
        'frais_port_min',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'conditions' => 'array',
        'coordonnees_bancaires' => 'array',
        'metadata' => 'array',
        'est_actif' => 'boolean',
        'delai_livraison_jours' => 'integer',
        'frais_port_min' => 'decimal:2',
    ];

    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'produit_fournisseur')
            ->withPivot('prix_achat_ht', 'delai_approvisionnement_jours', 'reference_fournisseur', 'est_principal')
            ->withTimestamps();
    }

    public function commandesAchat(): HasMany
    {
        return $this->hasMany(CommandeAchat::class);
    }

    public function getNbProduitsAttribute(): int
    {
        return $this->produits()->count();
    }

    public function getValeurStockAttribute(): float
    {
        return $this->produits()->sum('produit_fournisseur.prix_achat_ht');
    }

    public function scopeActif($query)
    {
        return $query->where('est_actif', true);
    }
}
