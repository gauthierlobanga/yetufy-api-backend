<?php

// app/Models/Taxe.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taxe extends Model
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
        'code',
        'taux',
        'pays',
        'region',
        'est_defaut',
    ];

    protected $casts = [
        'taux' => 'decimal:2',
        'est_defaut' => 'boolean',
    ];

    /**
     * Relations
     */
    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'produit_taxe');
    }

    /**
     * Accessors
     */
    public function getLibelleAttribute(): string
    {
        return "{$this->nom} ({$this->taux}%)";
    }

    /**
     * Méthodes métier
     */
    public function calculerMontant(float $montantHt): float
    {
        return $montantHt * ($this->taux / 100);
    }

    public function calculerTtc(float $montantHt): float
    {
        return $montantHt * (1 + $this->taux / 100);
    }
}
