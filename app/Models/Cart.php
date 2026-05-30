<?php

namespace App\Models;

use App\Concerns\Traits\BelongsToTenantConnection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use BelongsToTenantConnection;
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

    protected $casts = [
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
    ];

    const STATUT_ACTIF = 'actif';

    const STATUT_ABANDONNE = 'abandonne';

    const STATUT_CONVERTI = 'converti';

    const STATUT_EXPIRE = 'expire';

    /**
     * Relations
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemPanier::class)->orderBy('id');
    }

    public function livraison(): HasOne
    {
        return $this->hasOne(LivraisonPanier::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_panier')
            ->withPivot('montant_applique', 'applied_at')
            ->withTimestamps();
    }

    public function commande(): HasOne
    {
        return $this->hasOne(Commande::class);
    }

    /**
     * Accessors
     */
    public function getNbArticlesAttribute(): int
    {
        return $this->items->sum('quantite');
    }

    public function getEstVideAttribute(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Méthodes métier
     */
    public function ajouterItem(Produit $produit, int $quantite = 1, ?VarianteProduit $variante = null): ItemPanier
    {
        $item = $this->items()
            ->where('produit_id', $produit->id)
            ->where('variante_produit_id', $variante?->id)
            ->first();

        if ($item) {
            $item->quantite += $quantite;
            $item->prix_total = $item->quantite * $item->prix_unitaire;
            $item->save();
        } else {
            $prixUnitaire = $variante ? $variante->prix_actuel : $produit->prix_actuel;

            $item = $this->items()->create([
                'produit_id' => $produit->id,
                'variante_produit_id' => $variante?->id,
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'prix_total' => $prixUnitaire * $quantite,
                'taxe_unitaire' => $prixUnitaire - ($produit->prix_ht + ($variante?->supplement_prix ?? 0)),
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

    public function recalculerTotaux(): void
    {
        $this->sous_total = $this->items->sum('prix_total');
        $this->total_taxes = $this->items->sum(function ($item) {
            return $item->taxe_unitaire * $item->quantite;
        });
        $this->total_general = $this->sous_total + $this->total_taxes + $this->total_livraison - $this->total_remises;
        $this->date_modification = now();
        $this->save();
    }

    public function vider(): void
    {
        $this->items()->delete();
        $this->sous_total = 0;
        $this->total_taxes = 0;
        $this->total_livraison = 0;
        $this->total_remises = 0;
        $this->total_general = 0;
        $this->save();
    }

    public function convertirEnCommande(): Commande
    {
        $commande = $this->commande()->create([
            'client_id' => $this->client_id,
            'numero_commande' => $this->genererNumeroCommande(),
            'statut' => Commande::STATUT_EN_ATTENTE,
            'sous_total' => $this->sous_total,
            'taxe' => $this->total_taxes,
            'frais_livraison' => $this->total_livraison,
            'total' => $this->total_general,
            'date_commande' => now(),
        ]);

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
        return 'CMD-'.date('Ymd').'-'.strtoupper(uniqid());
    }
}
