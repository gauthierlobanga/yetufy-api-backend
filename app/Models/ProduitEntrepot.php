<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProduitEntrepot extends Pivot
{
    use HasUuids,SoftDeletes;

    protected $table = 'produit_entrepot';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'produit_id',
        'entrepot_id',
        'quantite',
        'quantite_reservee',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'quantite_reservee' => 'integer',
            'updated_at' => 'datetime',
        ];
    }

    // Relations
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function entrepot()
    {
        return $this->belongsTo(Entrepot::class);
    }

    // Accessors
    public function getQuantiteDisponibleAttribute(): int
    {
        return $this->quantite - $this->quantite_reservee;
    }

    public function getEstEnRuptureAttribute(): bool
    {
        return $this->quantite_disponible <= 0;
    }

    public function getEstEnAlerteAttribute(): bool
    {
        $seuilAlerte = $this->produit?->seuil_alerte ?? 5;

        return $this->quantite_disponible <= $seuilAlerte && $this->quantite_disponible > 0;
    }

    // Méthodes utilitaires
    public function incrementerStock(int $quantite): void
    {
        $this->quantite += $quantite;
        $this->updated_at = now();
        $this->save();
    }

    public function decrementerStock(int $quantite): bool
    {
        if ($this->quantite_disponible < $quantite) {
            return false;
        }

        $this->quantite -= $quantite;
        $this->updated_at = now();
        $this->save();

        return true;
    }

    public function reserver(int $quantite): bool
    {
        if ($this->quantite_disponible < $quantite) {
            return false;
        }

        $this->quantite_reservee += $quantite;
        $this->updated_at = now();
        $this->save();

        return true;
    }

    public function libererReservation(int $quantite): void
    {
        $this->quantite_reservee = max(0, $this->quantite_reservee - $quantite);
        $this->updated_at = now();
        $this->save();
    }

    public function confirmerReservation(int $quantite): void
    {
        $this->quantite -= $quantite;
        $this->quantite_reservee -= $quantite;
        $this->updated_at = now();
        $this->save();
    }

    public function synchroniser(int $nouvelleQuantite): void
    {
        $this->quantite = $nouvelleQuantite;
        $this->quantite_reservee = min($this->quantite_reservee, $nouvelleQuantite);
        $this->updated_at = now();
        $this->save();
    }
}
