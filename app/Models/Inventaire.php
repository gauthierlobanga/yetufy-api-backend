<?php

// app/Models/Inventaire.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventaire extends Model
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
        'entrepot_id',
        'reference',
        'statut',
        'resultats',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'resultats' => 'array',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    const STATUT_EN_COURS = 'en_cours';

    const STATUT_TERMINE = 'termine';

    const STATUT_VALIDE = 'valide';

    const STATUT_ANNULE = 'annuler';

    /**
     * Relations
     */
    public function entrepot(): BelongsTo
    {
        return $this->belongsTo(Entrepot::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }

    /**
     * Méthodes métier
     */
    // Accessors
    public function getLibelleStatutAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_TERMINE => 'Terminé',
            self::STATUT_VALIDE => 'Validé',
            self::STATUT_ANNULE => 'Annulé',
            default => $this->statut,
        };
    }

    public function getNbProduitsComptesAttribute(): int
    {
        return count($this->resultats['produits'] ?? []);
    }

    public function getNbEcartsAttribute(): int
    {
        $ecarts = 0;
        foreach ($this->resultats['produits'] ?? [] as $produit) {
            if (($produit['quantite_theorique'] ?? 0) != ($produit['quantite_reelle'] ?? 0)) {
                $ecarts++;
            }
        }

        return $ecarts;
    }

    public function getValeurEcartAttribute(): float
    {
        $total = 0;
        foreach ($this->resultats['produits'] ?? [] as $produit) {
            $ecart = ($produit['quantite_reelle'] ?? 0) - ($produit['quantite_theorique'] ?? 0);
            $total += $ecart * ($produit['prix_unitaire'] ?? 0);
        }

        return $total;
    }

    // Méthodes utilitaires
    public function demarrer(): void
    {
        $this->statut = self::STATUT_EN_COURS;
        $this->date_debut = now();
        $this->save();
    }

    public function terminer(array $resultats): void
    {
        $this->statut = self::STATUT_TERMINE;
        $this->date_fin = now();
        $this->resultats = $resultats;
        $this->save();
    }

    public function valider(): void
    {
        if ($this->statut !== self::STATUT_TERMINE) {
            throw new \Exception("L'inventaire doit être terminé avant d'être validé.");
        }

        $this->statut = self::STATUT_VALIDE;
        $this->save();

        // Créer les mouvements de stock pour les écarts
        foreach ($this->resultats['produits'] ?? [] as $produitData) {
            $ecart = ($produitData['quantite_reelle'] ?? 0) - ($produitData['quantite_theorique'] ?? 0);

            if ($ecart != 0) {
                MouvementStock::create([
                    'tenant_id' => $this->tenant_id,
                    'produit_id' => $produitData['produit_id'],
                    'entrepot_id' => $this->entrepot_id,
                    'inventaire_id' => $this->id,
                    'type' => MouvementStock::TYPE_AJUSTEMENT,
                    'quantite' => $ecart,
                    'reference' => "INV-{$this->reference}",
                    'notes' => 'Ajustement inventaire',
                    'date_mouvement' => now(),
                ]);

                // Mettre à jour le stock de l'entrepot
                if ($this->entrepot) {
                    $stockActuel = $this->entrepot->getStockProduit(Produit::find($produitData['produit_id']));
                    $this->entrepot->mettreAJourStock(
                        Produit::find($produitData['produit_id']),
                        $stockActuel + $ecart
                    );
                }
            }
        }
    }

    public function annuler(string $raison): void
    {
        $this->statut = self::STATUT_ANNULE;
        $this->resultats = array_merge($this->resultats ?? [], ['raison_annulation' => $raison]);
        $this->save();
    }

    public function ajouterProduit(Produit $produit, int $quantiteTheorique, ?int $quantiteReelle = null): void
    {
        $resultats = $this->resultats ?? ['produits' => []];
        $resultats['produits'][$produit->id] = [
            'produit_id' => $produit->id,
            'reference' => $produit->reference,
            'nom' => $produit->nom,
            'quantite_theorique' => $quantiteTheorique,
            'quantite_reelle' => $quantiteReelle,
            'prix_unitaire' => $produit->prix_ht,
        ];
        $this->resultats = $resultats;
        $this->save();
    }

    public function mettreAJourQuantiteReelle(Produit $produit, int $quantiteReelle): void
    {
        $resultats = $this->resultats ?? ['produits' => []];
        if (isset($resultats['produits'][$produit->id])) {
            $resultats['produits'][$produit->id]['quantite_reelle'] = $quantiteReelle;
            $this->resultats = $resultats;
            $this->save();
        }
    }

    // Scopes
    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    public function scopeTermines($query)
    {
        return $query->where('statut', self::STATUT_TERMINE);
    }

    public function scopeValides($query)
    {
        return $query->where('statut', self::STATUT_VALIDE);
    }

    public function scopeParEntrepot($query, $entrepotId)
    {
        return $query->where('entrepot_id', $entrepotId);
    }
}
