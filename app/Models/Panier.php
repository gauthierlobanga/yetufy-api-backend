<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float $sous_total
 * @property float $total_taxes
 * @property float $total_livraison
 * @property float $total_remises
 * @property float $total_general
 * @property int $nb_articles // accesseur
 * @property int $nb_produits_distincts
 * @property bool $est_vide
 * @property bool $est_abandonne
 * @property bool $est_converti
 * @property bool $est_actif
 * @property bool $est_expire
 * @property float $poids_total
 */
class Panier extends Model
{
    use HasUuids, SoftDeletes;

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

    protected $table = 'paniers';

    protected $fillable = [
        'client_id',
        'user_id',
        'session_id',
        'statut',
        'sous_total',
        'total_taxes',
        'total_livraison',
        'total_remises',
        'total_general',
        'metadata',
        'date_creation',
        'date_modification',
        'date_abandon',
        'date_conversion',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'sous_total' => 'decimal:2',
            'total_taxes' => 'decimal:2',
            'total_livraison' => 'decimal:2',
            'total_remises' => 'decimal:2',
            'total_general' => 'decimal:2',
            'date_creation' => 'datetime',
            'date_modification' => 'datetime',
            'date_abandon' => 'datetime',
            'date_conversion' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Constantes
    const STATUT_ACTIF = 'actif';

    const STATUT_ABANDONNE = 'abandonne';

    const STATUT_CONVERTI = 'converti';

    const STATUT_EXPIRE = 'expire';

    public static function getStatuts(): array
    {
        return [
            self::STATUT_ACTIF,
            self::STATUT_ABANDONNE,
            self::STATUT_CONVERTI,
            self::STATUT_EXPIRE,
        ];
    }

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les items du panier
     *
     * @return HasMany<ItemPanier>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemPanier::class);
    }

    public function livraison(): HasOne
    {
        return $this->hasOne(LivraisonPanier::class);
    }

    public function regles(): HasMany
    {
        return $this->hasMany(ReglePanier::class);
    }

    public function abandon(): HasOne
    {
        return $this->hasOne(AbandonPanier::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_panier')
            ->using(PromotionPanier::class)
            ->withPivot('montant_applique', 'applied_at')
            ->withTimestamps();
    }

    public function getPromotionsAppliquees()
    {
        return $this->promotions()->get();
    }

    public function getTotalReductionsPromotions(): float
    {
        return $this->promotions()
            ->sum('promotion_panier.montant_applique');
    }

    public function commande(): HasOne
    {
        return $this->hasOne(Commande::class);
    }

    public function getNbArticlesAttribute(): int
    {
        return $this->items->sum('quantite');
    }

    public function getNbProduitsDistinctsAttribute(): int
    {
        return $this->items->count();
    }

    public function getEstVideAttribute(): bool
    {
        return $this->items->isEmpty();
    }

    public function getEstAbandonneAttribute(): bool
    {
        return $this->statut === self::STATUT_ABANDONNE;
    }

    public function getEstConvertiAttribute(): bool
    {
        return $this->statut === self::STATUT_CONVERTI;
    }

    public function getEstActifAttribute(): bool
    {
        return $this->statut === self::STATUT_ACTIF && ! $this->estExpire;
    }

    public function getEstExpireAttribute(): bool
    {
        return $this->expires_at && $this->expires_at?->isPast();
    }

    public function getPoidsTotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->produit->poids ?? 0) * $item->quantite;
        });
    }

    // Méthodes utilitaires
    public function ajouterItem(Produit $produit, int $quantite = 1, ?VarianteProduit $variante = null): ItemPanier
    {
        // Vérifier si l'item existe déjà
        $item = $this->items()
            ->where('produit_id', $produit->id)
            ->where('variante_produit_id', $variante?->id)
            ->first();

        if ($item) {
            $item->quantite += $quantite;
            $item->prix_total = $item->quantite * $item->prix_unitaire;
            $item->save();
        } else {
            $prixUnitaire = $variante ? $produit->prix_ttc + $variante->supplement_prix : $produit->prix_ttc;

            $item = $this->items()->create([
                'produit_id' => $produit->id,
                'variante_produit_id' => $variante?->id,
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'prix_total' => $prixUnitaire * $quantite,
                'taxe_unitaire' => $prixUnitaire - $produit->prix_ht,
                'added_at' => now(),
            ]);
        }

        $this->recalculerTotaux();

        return $item;
    }

    public function retirerItem(string $itemId): void
    {
        $this->items()->where('id', $itemId)->delete();
        $this->recalculerTotaux();
    }

    public function mettreAJourQuantite(string $itemId, int $quantite): void
    {
        $item = $this->items()->findOrFail($itemId);
        $item->quantite = $quantite;
        $item->prix_total = $quantite * $item->prix_unitaire;
        $item->save();

        $this->recalculerTotaux();
    }

    public function recalculerTotaux(): void
    {
        $this->sous_total = $this->items->sum('prix_total');
        $this->total_taxes = $this->items->sum('taxe_unitaire') * $this->items->sum('quantite');
        $this->total_remises = $this->regles->where('appliquee', true)->sum('resultat.montant');
        $this->total_general = $this->sous_total + $this->total_taxes + $this->total_livraison - $this->total_remises;
        $this->date_modification = now();
        $this->save();
    }

    public function appliquerPromotion(Promotion $promotion): bool
    {
        if (! $promotion->estApplicable($this, $this->client)) {
            return false;
        }

        $montantReduction = $promotion->calculerReduction($this);

        $this->promotions()->attach($promotion->id, [
            'montant_applique' => $montantReduction,
            'applied_at' => now(),
        ]);

        $promotion->incrementerUtilisation($this->client);

        $this->recalculerTotaux();

        return true;
    }

    public function appliquerLivraison(string $mode, float $cout, ?Adresse $adresse = null): void
    {
        $livraison = $this->livraison()->firstOrNew([]);
        $livraison->mode = $mode;
        $livraison->cout = $cout;
        $livraison->adresse_id = $adresse?->id;
        $livraison->selected_at = now();
        $livraison->save();

        $this->total_livraison = $cout;
        $this->recalculerTotaux();
    }

    public function marquerAbandonne(string $etape, ?string $raison = null): AbandonPanier
    {
        $this->statut = self::STATUT_ABANDONNE;
        $this->date_abandon = now();
        $this->save();

        $abandon = $this->abandon()->create([
            'raison' => $raison,
            'etape_abandon' => $etape,
            'analytics_data' => [
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
                'url' => request()->url(),
            ],
        ]);

        return $abandon;
    }

    public function convertirEnCommande(): Commande
    {
        $commande = $this->commande()->create([
            'tenant_id' => $this->tenant_id,
            'client_id' => $this->client_id,
            'numero_commande' => $this->genererNumeroCommande(),
            'statut' => Commande::STATUT_EN_ATTENTE,
            'sous_total' => $this->sous_total,
            'taxe' => $this->total_taxes,
            'frais_livraison' => $this->total_livraison,
            'total' => $this->total_general,
            'adresse_facturation_id' => $this->client?->adresse_facturation?->id,
            'adresse_livraison_id' => $this->livraison?->adresse_id,
            'date_commande' => now(),
        ]);

        // Copier les items
        foreach ($this->items as $item) {
            $commande->lignes()->create([
                'produit_id' => $item->produit_id,
                'variante_produit_id' => $item->variante_produit_id,
                'quantite' => $item->quantite,
                'prix_unitaire' => $item->prix_unitaire,
                'prix_total' => $item->prix_total,
                'taxe' => $item->taxe_unitaire * $item->quantite,
                'options' => $item->options_selectionnees,
            ]);
        }

        $this->statut = self::STATUT_CONVERTI;
        $this->date_conversion = now();
        $this->save();

        return $commande;
    }

    private function genererNumeroCommande(): string
    {
        $prefix = 'CMD';
        $year = now()->format('Y');
        $month = now()->format('m');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$prefix}-{$year}{$month}-{$random}";
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('statut', self::STATUT_ACTIF)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeAbandonnes($query)
    {
        return $query->where('statut', self::STATUT_ABANDONNE);
    }

    public function scopeConvertis($query)
    {
        return $query->where('statut', self::STATUT_CONVERTI);
    }

    public function scopeParSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeParClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeExpires($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Vider complètement le panier (supprime tous les items).
     */
    public function vider(): void
    {
        $this->items()->delete();
        $this->recalculerTotaux();
    }
}
