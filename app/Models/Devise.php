<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devise extends Model
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
        'code',
        'symbole',
        'taux_change',
        'est_reference',
    ];

    protected $casts = [
        'taux_change' => 'decimal:4',
        'est_reference' => 'boolean',
    ];

    /**
     * Relations
     */
    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }

    /**
     * Accessors
     */
    public function getLibelleAttribute(): string
    {
        return "{$this->code} ({$this->symbole})";
    }

    /**
     * Méthodes métier
     */
    public function convertir(float $montant, Devise $cible): float
    {
        if ($this->est_reference) {
            return $montant * $cible->taux_change;
        }

        if ($cible->est_reference) {
            return $montant / $this->taux_change;
        }

        $montantReference = $montant / $this->taux_change;

        return $montantReference * $cible->taux_change;
    }

    public function format(float $montant): string
    {
        return number_format($montant, 2).' '.$this->code;
    }

    // Dans app/Models/Devise.php, ajoutez ces méthodes :

    // Obtenir la devise par défaut
    public static function getReference(): ?self
    {
        return self::where('est_reference', true)->first();
    }

    // Convertir depuis une autre devise
    public function convertirDepuis(float $montant, Devise $source): float
    {
        return $source->convertir($montant, $this);
    }

    // Formater avec le symbole
    public function getFormattedSymbolAttribute(): string
    {
        return match ($this->code) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'JPY' => '¥',
            'CHF' => 'CHF',
            default => $this->symbole,
        };
    }

    // Obtenir le nom complet
    public function getFullNameAttribute(): string
    {
        $names = [
            'EUR' => 'Euro',
            'USD' => 'Dollar américain',
            'GBP' => 'Livre sterling',
            'JPY' => 'Yen japonais',
            'CHF' => 'Franc suisse',
            'CAD' => 'Dollar canadien',
            'AUD' => 'Dollar australien',
        ];

        return $names[$this->code] ?? $this->code;
    }

    // Scope pour les devises actives (non supprimées)
    public function scopeActives($query)
    {
        return $query->whereNull('deleted_at');
    }
}
