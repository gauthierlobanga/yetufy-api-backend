<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Client extends Model
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

    protected $table = 'clients';

    protected $fillable = [
        'user_id',
        'type',
        'civilite',
        'nom',
        'prenom',
        'societe',
        'siret',
        'code_tva',
        'email',
        'telephone',
        'portable',
        'fax',
        'site_web',
        'notes',
        'preferences',
        'date_premier_achat',
        'date_dernier_achat',
        'date_derniere_connexion',
        'total_achats',
        'nombre_commandes',
        'total_remises',
        'chiffre_affaire',
        'points_fidelite',
        'niveau_fidelite',
        'statut',
        'source',
        'metadata',
    ];

    protected $casts = [
        'preferences' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
        'date_premier_achat' => 'datetime',
        'date_dernier_achat' => 'datetime',
        'date_derniere_connexion' => 'datetime',
        'total_achats' => 'decimal:2',
        'total_remises' => 'decimal:2',
        'chiffre_affaire' => 'decimal:2',
        'points_fidelite' => 'integer',
    ];

    // Constantes
    const TYPE_PARTICULIER = 'particulier';

    const TYPE_PROFESSIONNEL = 'professionnel';

    const TYPE_ENTREPRISE = 'entreprise';

    const CIVILITE_M = 'M.';

    const CIVILITE_MME = 'Mme';

    const CIVILITE_MLLE = 'Mlle';

    const STATUT_ACTIF = 'actif';

    const STATUT_INACTIF = 'inactif';

    const STATUT_SUSPENDU = 'suspendu';

    const STATUT_FIDELISE = 'fidelise';

    const STATUT_VIP = 'vip';

    const SOURCE_DIRECT = 'direct';

    const SOURCE_GOOGLE = 'google';

    const SOURCE_FACEBOOK = 'facebook';

    const SOURCE_INSTAGRAM = 'instagram';

    const SOURCE_REFERAL = 'referal';

    const SOURCE_NEWSLETTER = 'newsletter';

    const NIVEAU_BRONZE = 'bronze';

    const NIVEAU_ARGENT = 'argent';

    const NIVEAU_OR = 'or';

    const NIVEAU_PLATINE = 'platine';

    const NIVEAU_DIAMANT = 'diamant';

    public static function getTypes(): array
    {
        return [
            self::TYPE_PARTICULIER => 'Particulier',
            self::TYPE_PROFESSIONNEL => 'Professionnel',
            self::TYPE_ENTREPRISE => 'Entreprise',
        ];
    }

    public static function getCivilites(): array
    {
        return [
            self::CIVILITE_M => 'Monsieur',
            self::CIVILITE_MME => 'Madame',
            self::CIVILITE_MLLE => 'Mademoiselle',
        ];
    }

    public static function getStatuts(): array
    {
        return [
            self::STATUT_ACTIF => 'Actif',
            self::STATUT_INACTIF => 'Inactif',
            self::STATUT_SUSPENDU => 'Suspendu',
            self::STATUT_FIDELISE => 'Fidélisé',
            self::STATUT_VIP => 'VIP',
        ];
    }

    public static function getSources(): array
    {
        return [
            self::SOURCE_DIRECT => 'Direct',
            self::SOURCE_GOOGLE => 'Google',
            self::SOURCE_FACEBOOK => 'Facebook',
            self::SOURCE_INSTAGRAM => 'Instagram',
            self::SOURCE_REFERAL => 'Parrainage',
            self::SOURCE_NEWSLETTER => 'Newsletter',
        ];
    }

    public static function getNiveauxFidelite(): array
    {
        return [
            self::NIVEAU_BRONZE => 'Bronze',
            self::NIVEAU_ARGENT => 'Argent',
            self::NIVEAU_OR => 'Or',
            self::NIVEAU_PLATINE => 'Platine',
            self::NIVEAU_DIAMANT => 'Diamant',
        ];
    }

    // ========== RELATIONS ==========

    /**
     * Relation avec l'utilisateur associé (si applicable)
     *
     * @return BelongsTo<User, Client>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adresses(): MorphMany
    {
        return $this->morphMany(Adresse::class, 'addressable');
    }

    /**
     * Relations avec les autres entités
     *
     * @return HasMany<Panier>, HasMany<Commande>, HasMany<AvisClient>, HasMany<Wishlist>, BelongsToMany<Promotion>, HasMany<CompteFidelite>, HasMany<Abonnement>
     */
    public function paniers(): HasMany
    {
        return $this->hasMany(Panier::class);
    }

    /**
     * Relation avec les commandes
     *
     * @return HasMany<Commande, Client>
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function commandesRecentes($limit = 5)
    {
        return $this->commandes()->latest()->limit($limit)->get();
    }

    /**
     * Relation avec les avis clients
     *
     * @return HasMany<AvisClient, Client>
     */
    public function avis(): HasMany
    {
        return $this->hasMany(AvisClient::class);
    }

    /**
     * Relation avec les wishlists
     *
     * @return HasMany<Wishlist, Client>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Relation avec les promotions (many-to-many)
     *
     * @return BelongsToMany<Promotion, Client, Pivot>
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_client')
            ->using(PromotionClient::class)
            ->withPivot('utilisations', 'premiere_utilisation', 'derniere_utilisation')
            ->withTimestamps();
    }

    /**
     * Le front client manipule un compte fidélité principal par client.
     *
     * @return HasOne<CompteFidelite, Client>
     */
    public function compteFidelite(): HasOne
    {
        return $this->hasOne(CompteFidelite::class);
    }

    /**
     * Relation avec les abonnements (ex: newsletter, programmes de fidélité, etc.)
     *
     * @return HasMany<Abonnement, Client>
     */
    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }

    public function newsletter(): HasOne
    {
        return $this->hasOne(Newsletter::class, 'email', 'email');
    }

    public function parrainages(): HasMany
    {
        return $this->hasMany(Client::class, 'parrain_id');
    }

    public function parrain(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'parrain_id');
    }

    public function ticketsSupport(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function devis(): HasMany
    {
        return $this->hasMany(Devis::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    public function relances(): HasMany
    {
        return $this->hasMany(RelanceClient::class);
    }

    /**
     * Relation polymorphique avec les notifications
     *
     * @return MorphMany<Notification, Client>
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    // ========== ACCESSORS ==========

    public function getFullNameAttribute(): string
    {
        if ($this->type === self::TYPE_ENTREPRISE && $this->societe) {
            return $this->societe;
        }
        if ($this->type === self::TYPE_PROFESSIONNEL && $this->societe) {
            return $this->societe.' ('.$this->nom.' '.$this->prenom.')';
        }

        return trim($this->civilite.' '.$this->prenom.' '.$this->nom);
    }

    public function getPanierMoyenAttribute(): float
    {
        if ($this->nombre_commandes === 0) {
            return 0;
        }

        return $this->total_achats / $this->nombre_commandes;
    }

    public function getChiffreAffaireAttribute(): float
    {
        return $this->commandes()->where('statut', 'termine')->sum('total');
    }

    public function getTauxFidelisationAttribute(): float
    {
        if ($this->nombre_commandes === 0) {
            return 0;
        }
        $commandesRecurrentes = $this->commandes()
            ->where('statut', 'termine')
            ->whereRaw('DATE_PART(\'month\', date_commande) = DATE_PART(\'month\', NOW())')
            ->count();

        return ($commandesRecurrentes / $this->nombre_commandes) * 100;
    }

    public function getDerniereActiviteAttribute(): ?string
    {
        $dates = [];
        if ($this->date_dernier_achat) {
            $dates[] = $this->date_dernier_achat;
        }
        if ($this->date_derniere_connexion) {
            $dates[] = $this->date_derniere_connexion;
        }
        if ($this->paniers()->where('date_modification', '>', now()->subDays(30))->exists()) {
            $dates[] = $this->paniers()->latest('date_modification')->first()->date_modification;
        }

        if (empty($dates)) {
            return null;
        }

        return max($dates)->diffForHumans();
    }

    public function getEstActifAttribute(): bool
    {
        $lastActivity = $this->derniere_activite;
        if (! $lastActivity) {
            return false;
        }

        return $this->date_dernier_achat && $this->date_dernier_achat->diffInDays(now()) <= 90;
    }

    public function getNiveauFideliteLibelleAttribute(): string
    {
        $seuils = [
            self::NIVEAU_BRONZE => 0,
            self::NIVEAU_ARGENT => 500,
            self::NIVEAU_OR => 2000,
            self::NIVEAU_PLATINE => 5000,
            self::NIVEAU_DIAMANT => 10000,
        ];

        foreach ($seuils as $niveau => $seuil) {
            if ($this->total_achats >= $seuil) {
                return self::getNiveauxFidelite()[$niveau];
            }
        }

        return self::getNiveauxFidelite()[self::NIVEAU_BRONZE];
    }

    // ========== SCOPES ==========

    public function scopeParticulier($query)
    {
        return $query->where('type', self::TYPE_PARTICULIER);
    }

    public function scopeProfessionnel($query)
    {
        return $query->where('type', self::TYPE_PROFESSIONNEL);
    }

    public function scopeEntreprise($query)
    {
        return $query->where('type', self::TYPE_ENTREPRISE);
    }

    public function scopeActif($query)
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }

    public function scopeAyantCommandes($query)
    {
        return $query->has('commandes');
    }

    public function scopeSansCommande($query)
    {
        return $query->doesntHave('commandes');
    }

    public function scopeAvecPanierAbandonne($query)
    {
        return $query->whereHas('paniers', function ($q) {
            $q->where('statut', 'abandonne');
        });
    }

    public function scopeFidelise($query)
    {
        return $query->where('statut', self::STATUT_FIDELISE)
            ->orWhere('statut', self::STATUT_VIP);
    }

    public function scopeParSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeChiffreAffaireSup($query, float $montant)
    {
        return $query->whereHas('commandes', function ($q) use ($montant) {
            $q->where('statut', 'termine')
                ->havingRaw('SUM(total) > ?', [$montant]);
        });
    }

    // ========== MÉTHODES MÉTIER ==========

    public function estProfessionnel(): bool
    {
        return $this->type === self::TYPE_PROFESSIONNEL || $this->type === self::TYPE_ENTREPRISE;
    }

    public function estParticulier(): bool
    {
        return $this->type === self::TYPE_PARTICULIER;
    }

    public function estActif(): bool
    {
        return $this->statut === self::STATUT_ACTIF;
    }

    public function estVIP(): bool
    {
        return $this->statut === self::STATUT_VIP || $this->total_achats >= 5000;
    }

    public function incrementerCommandes(float $montant): void
    {
        $this->nombre_commandes++;
        $this->total_achats += $montant;
        $this->date_dernier_achat = now();

        if (! $this->date_premier_achat) {
            $this->date_premier_achat = now();
        }

        $this->mettreAJourNiveauFidelite();
        $this->save();
    }

    public function mettreAJourNiveauFidelite(): void
    {
        if ($this->total_achats >= 10000) {
            $this->niveau_fidelite = self::NIVEAU_DIAMANT;
            $this->statut = self::STATUT_VIP;
        } elseif ($this->total_achats >= 5000) {
            $this->niveau_fidelite = self::NIVEAU_PLATINE;
            $this->statut = self::STATUT_VIP;
        } elseif ($this->total_achats >= 2000) {
            $this->niveau_fidelite = self::NIVEAU_OR;
            $this->statut = self::STATUT_FIDELISE;
        } elseif ($this->total_achats >= 500) {
            $this->niveau_fidelite = self::NIVEAU_ARGENT;
            $this->statut = self::STATUT_FIDELISE;
        } else {
            $this->niveau_fidelite = self::NIVEAU_BRONZE;
        }
    }

    /**
     * Obtenir les promotions utilisables par le client
     *
     * @return Collection
     */
    public function getPromotionsUtilisables()
    {
        return Promotion::whereHas('clients', function ($query) {
            $query->where('client_id', $this->id);
        })
            ->where(function ($query) {
                // Promotions sans limite d'utilisation
                $query->whereNull('utilisation_max')
                    // Ou promotions avec limite non atteinte
                    ->orWhere(function ($q) {
                        $q->whereNotNull('utilisation_max')
                            ->whereExists(function ($subQuery) {
                                $subQuery->selectRaw('1')
                                    ->from('promotion_client')
                                    ->whereColumn('promotion_client.promotion_id', 'promotions.id')
                                    ->where('promotion_client.client_id', $this->id)
                                    ->whereColumn('promotion_client.utilisations', '<', 'promotions.utilisation_max');
                            });
                    });
            })
            ->where('date_debut', '<=', now())
            ->where(function ($query) {
                $query->whereNull('date_fin')
                    ->orWhere('date_fin', '>=', now());
            })
            ->get();
    }

    public function peutUtiliserPromotion(Promotion $promotion): bool
    {
        if (! $promotion->est_active) {
            return false;
        }

        $pivot = $this->promotions()
            ->where('promotion_id', $promotion->id)
            ->first();

        if (! $pivot) {
            return false;
        }

        if ($promotion->utilisation_max && $pivot->pivot->utilisations >= $promotion->utilisation_max) {
            return false;
        }

        return true;
    }

    public function ajouterPointsFidelite(int $points, string $raison): void
    {
        $this->points_fidelite += $points;
        $this->save();

        // Créer une transaction de fidélité
        TransactionFidelite::create([
            'client_id' => $this->id,
            'type' => 'gain',
            'points' => $points,
            'raison' => $raison,
        ]);
    }

    public function utiliserPointsFidelite(int $points, string $raison): bool
    {
        if ($this->points_fidelite < $points) {
            return false;
        }

        $this->points_fidelite -= $points;
        $this->save();

        TransactionFidelite::create([
            'client_id' => $this->id,
            'type' => 'utilisation',
            'points' => -$points,
            'raison' => $raison,
        ]);

        return true;
    }

    public function getRecommandationsProduits(int $limit = 5)
    {
        // Produits les plus achetés par le client
        $produitsAchetes = $this->commandes()
            ->with('items.produit')
            ->get()
            ->pluck('items')
            ->flatten()
            ->pluck('produit')
            ->unique('id');

        // Catégories préférées
        $categoriesPreferees = $produitsAchetes->pluck('categories')->flatten()->unique('id');

        // Produits similaires des mêmes catégories
        return Produit::whereHas('categories', function ($q) use ($categoriesPreferees) {
            $q->whereIn('categories.id', $categoriesPreferees->pluck('id'));
        })
            ->whereNotIn('id', $produitsAchetes->pluck('id'))
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function getValeurVieClient(): float
    {
        // Valeur Vie Client = (Panier moyen x Fréquence d'achat) x Durée de vie
        $frequenceMoyenne = $this->nombre_commandes / max(1, $this->date_premier_achat?->diffInMonths(now()) ?? 1);

        return $this->panier_moyen * $frequenceMoyenne * 36; // 3 ans
    }

    public function envoyerEmail(string $sujet, string $contenu): void
    {
        // Envoi d'email via notification
        Notification::create([
            'notifiable_type' => Client::class,
            'notifiable_id' => $this->id,
            'type' => 'email',
            'sujet' => $sujet,
            'contenu' => $contenu,
            'statut' => 'en_attente',
        ]);
    }

    public function getStatistiquesMensuelles(): array
    {
        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();

        $commandesMois = $this->commandes()
            ->whereBetween('date_commande', [$debutMois, $finMois])
            ->where('statut', 'termine');

        return [
            'commandes' => $commandesMois->count(),
            'chiffre_affaire' => $commandesMois->sum('total'),
            'panier_moyen' => $commandesMois->avg('total'),
            'evolution' => $this->calculerEvolutionMensuelle(),
        ];
    }

    protected function calculerEvolutionMensuelle(): float
    {
        $moisPrecedent = now()->subMonth();
        $commandesMoisPrecedent = $this->commandes()
            ->whereMonth('date_commande', $moisPrecedent->month)
            ->whereYear('date_commande', $moisPrecedent->year)
            ->where('statut', 'termine')
            ->sum('total');

        $commandesMoisActuel = $this->commandes()
            ->whereMonth('date_commande', now()->month)
            ->whereYear('date_commande', now()->year)
            ->where('statut', 'termine')
            ->sum('total');

        if ($commandesMoisPrecedent == 0) {
            return $commandesMoisActuel > 0 ? 100 : 0;
        }

        return (($commandesMoisActuel - $commandesMoisPrecedent) / $commandesMoisPrecedent) * 100;
    }

    // ========== BOOT ==========

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($client) {
            if (empty($client->statut)) {
                $client->statut = self::STATUT_ACTIF;
            }
        });

        static::created(function ($client) {
            Cache::tags(['clients'])->flush();
        });

        static::updated(function ($client) {
            Cache::tags(['clients'])->flush();
        });

        static::deleted(function ($client) {
            Cache::tags(['clients'])->flush();
        });
    }
}
