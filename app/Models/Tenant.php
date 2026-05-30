<?php

namespace App\Models;

// use App\Observers\TenantObserver;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

// #[ObservedBy(TenantObserver::class)]
class Tenant extends BaseTenant implements HasAvatar, HasCurrentTenantLabel, HasMedia, HasName, TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    use InteractsWithMedia, SoftDeletes;

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
        'id',
        'user_id',
        'raison_sociale',
        'slug',
        'siret',
        'email',
        'password',
        'telephone',
        'is_active',
        'type_entite',
        'configuration',
        'statut',
        'date_activation',
        'date_expiration',
        'metadata',
        'data',
        'plan_id',
        'description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'raison_sociale',
            'slug',
            'domain',
            'siret',
            'email',
            'password',
            'telephone',
            'is_active',
            'type_entite',
            'statut',
            'date_activation',
            'date_expiration',
            'configuration',
            'metadata',
        ];
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'configuration' => 'array',
            'metadata' => 'array',
            'data' => 'array',
            'date_activation' => 'datetime',
            'date_expiration' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Types d'entités en RDC
    public const string TYPE_SARL = 'SARL';

    public const string TYPE_SA = 'SA';

    public const string TYPE_SUARL = 'SUARL';

    public const string TYPE_SNC = 'SNC';

    public const string TYPE_SCS = 'SCS';

    public const string TYPE_ASBL = 'ASBL';

    public const string TYPE_ONG = 'ONG';

    public const string TYPE_ETABLISSEMENT = 'ETABLISSEMENT';

    public const string TYPE_COOPERATIVE = 'COOPERATIVE';

    public const string TYPE_ARTISAN_INDIVIDUEL = 'ARTISAN_INDIVIDUEL';

    public const string TYPE_ENTREPRISE_INDIVIDUELLE = 'ENTREPRISE_INDIVIDUELLE';

    public const string TYPE_GIE = 'GIE';

    // Statuts
    public const string STATUT_ACTIF = 'actif';

    public const string STATUT_INACTIF = 'inactif';

    public const string STATUT_EN_ATTENTE = 'en_attente';

    public const string STATUT_SUSPENDU = 'suspendu';

    public static function getStatuts(): array
    {
        return [
            self::STATUT_ACTIF => 'Actif',
            self::STATUT_INACTIF => 'Inactif',
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_SUSPENDU => 'Suspendu',
        ];
    }

    public static function getTypesEntite(): array
    {
        return [
            self::TYPE_ARTISAN_INDIVIDUEL => 'Artisan Individuel',
            self::TYPE_ENTREPRISE_INDIVIDUELLE => 'Entreprise Individuelle',
            self::TYPE_SUARL => 'SUARL',
            self::TYPE_SARL => 'SARL',
            self::TYPE_SA => 'S.A',
            self::TYPE_SNC => 'SNC',
            self::TYPE_SCS => 'SCS',
            self::TYPE_GIE => 'GIE',
            self::TYPE_COOPERATIVE => 'Coopérative',
            self::TYPE_ASBL => 'ASBL',
            self::TYPE_ONG => 'ONG',
            self::TYPE_ETABLISSEMENT => 'Éts Public',
        ];
    }

    public function setPasswordAttribute(?string $value): void
    {
        if (! empty($value)) {
            $this->attributes['password'] = Hash::needsRehash($value)
                ? Hash::make($value)
                : $value;
        }
    }

    public function documentsLegaux()
    {
        return $this->belongsToMany(
            TypeDocumentLegal::class,
            'tenant_documents_legaux',
            'tenant_id',
            'type_document_id'
        )
            ->withPivot([
                'id',
                'numero_document',
                'date_delivrance',
                'date_expiration',
                'lieu_delivrance',
                'autorite_delivrance',
                'metadata',
                'est_verifie',
                'verifie_le',
                'verifie_par',
                'created_at',
                'updated_at',
            ])
            ->withTimestamps();
    }

    // Accesseurs pour les documents courants
    public function getRccmAttribute()
    {
        return $this->documentsLegaux()
            ->where('code', 'RCCM')
            ->first()?->pivot->numero_document;
    }

    public function getPatenteAttribute()
    {
        return $this->documentsLegaux()
            ->where('code', 'PATENTE')
            ->first()?->pivot->numero_document;
    }

    public function getIfuAttribute()
    {
        return $this->documentsLegaux()
            ->where('code', 'IFU')
            ->first()?->pivot->numero_document;
    }

    public function getNumeroImpotAttribute()
    {
        return $this->ifu;
    }

    public function documentsObligatoiresComplets(): bool
    {
        $obligatoires = TypeDocumentLegal::obligatoires()->count();
        $fournis = $this->documentsLegaux()
            ->where('est_obligatoire', true)
            ->whereNotNull('tenant_documents_legaux.numero_document')
            ->count();

        return $obligatoires === $fournis;
    }

    public function getPourcentageVerificationAttribute(): float
    {
        $total = TypeDocumentLegal::count();
        if ($total === 0) {
            return 0;
        }
        $verifies = $this->documentsLegaux()
            ->where('tenant_documents_legaux.est_verifie', true)
            ->count();

        return round(($verifies / $total) * 100, 1);
    }

    public function getDocumentsManquantsAttribute(): array
    {
        $obligatoires = TypeDocumentLegal::obligatoires()->pluck('code');
        $fournis = $this->documentsLegaux()
            ->whereNotNull('tenant_documents_legaux.numero_document')
            ->pluck('code')
            ->toArray();

        return $obligatoires->diff($fournis)->values()->toArray();
    }

    // Relations existantes

    public function getFilamentName(): string
    {
        return "{$this->raison_sociale}";
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tenant', 'tenant_id', 'user_id')
            ->using(UserTenantPivot::class)
            ->withPivot('user_id', 'tenant_id', 'is_owner')
            ->withTimestamps();
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('tenant_avatar')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'image/svg+xml',
            ])
            ->useDisk('public');
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('tenant_thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();
    }

    public function isAccessible(): bool
    {
        return $this->vendorRequest()
            ->where('status', VendorRequest::STATUS_APPROVED)
            ->exists() && $this->estActif();
    }

    public function vendorRequest(): HasOne
    {
        return $this->hasOne(VendorRequest::class, 'tenant_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logo_url;
    }

    /**
     * Méthode utilisée par Filament pour obtenir le nom du tenant
     */
    public function getTenantName(): string
    {
        return $this->raison_sociale
            ?? $this->slug
            ?? "Vendeur #{$this->getKey()}";
    }

    public function getProduitsCountAttribute(): int
    {
        return once(function () {
            try {
                return $this->run(function () {
                    return Produit::count();
                });
            } catch (\Exception $e) {
                return 0;
            }
        });
    }

    /**
     * Route key name pour Filament
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /** @return HasMany<AbandonPanier, self> */
    public function abandonPaniers(): HasMany
    {
        return $this->hasMany(AbandonPanier::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /** @return HasMany<Abonnement, self> */
    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }

    /** @return HasMany<Marketing, self> */
    public function Marketings(): HasMany
    {
        return $this->hasMany(Marketing::class);
    }

    /** @return HasMany<PostCategoryPivot, self> */
    public function categoriePostPivots(): HasMany
    {
        return $this->hasMany(PostCategoryPivot::class);
    }

    /** @return HasMany<PostCategory, self> */
    public function categoriePosts(): HasMany
    {
        return $this->hasMany(PostCategory::class);
    }

    /** @return HasMany<CommandeAchat, self> */
    public function commandeAchats(): HasMany
    {
        return $this->hasMany(CommandeAchat::class);
    }

    /** @return HasMany<CompteFidelite, self> */
    public function compteFidelites(): HasMany
    {
        return $this->hasMany(CompteFidelite::class);
    }

    /** @return HasMany<MouvementStock, self> */
    public function mouvementStocks(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function categoriesPostsPivots(): HasMany
    {
        return $this->hasMany(PostCategoryPivot::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }

    public function paniers(): HasMany
    {
        return $this->hasMany(Panier::class);
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(PostCategory::class);
    }

    public function categoriesPosts(): HasMany
    {
        return $this->hasMany(PostCategory::class);
    }

    public function fournisseurs(): HasMany
    {
        return $this->hasMany(Fournisseur::class);
    }

    public function entrepots(): HasMany
    {
        return $this->hasMany(Entrepot::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(Taxe::class);
    }

    public function devises(): HasMany
    {
        return $this->hasMany(Devise::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function campagnesMarketing(): HasMany
    {
        return $this->hasMany(CampagneMarketing::class);
    }

    public function statistiques(): HasMany
    {
        return $this->hasMany(Statistique::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function programmesFidelity(): HasMany
    {
        return $this->hasMany(ProgrammeFidelite::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function retours(): HasMany
    {
        return $this->hasMany(Retour::class);
    }

    public function mouvementsStock(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function inventaires(): HasMany
    {
        return $this->hasMany(Inventaire::class);
    }

    public function commandesAchat(): HasMany
    {
        return $this->hasMany(CommandeAchat::class);
    }

    public function avisClients(): HasMany
    {
        return $this->hasMany(AvisClient::class);
    }

    // URLs
    public function getUrlAttribute(): string
    {
        if ($domain = $this->domains()->value('domain')) {
            return 'http://'.$domain;
        }

        return 'http://'.$this->slug.'.'.config('app.domain');
    }

    public function getAdminUrlAttribute(): string
    {
        return $this->url.'/vendeur';
    }

    public function estActif(): bool
    {
        return $this->statut === self::STATUT_ACTIF && $this->is_active;
    }

    public function estExpire(): bool
    {
        return $this->date_expiration && $this->date_expiration->isPast();
    }

    public function getConfiguration(string $key, $default = null)
    {
        return data_get($this->configuration, $key, $default);
    }

    public function setConfiguration(string $key, $value): self
    {
        $configuration = $this->configuration ?? [];
        data_set($configuration, $key, $value);
        $this->configuration = $configuration;

        return $this;
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Boutique';
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {

            if (blank($tenant->id)) {
                $tenant->id = (string) Str::orderedUuid();
            }

            if (blank($tenant->slug)) {

                $baseSlug = Str::slug($tenant->raison_sociale);
                $slug = $baseSlug;
                $count = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }

                $tenant->slug = $slug;
            }
        });

        static::deleting(function ($tenant) {
            // Détache tous les utilisateurs liés avant de supprimer le tenant
            $tenant->users()->detach();
        });
    }

    /**
     * Vérifie si la période d'essai du plan est expirée.
     */
    public function isTrialExpired(): bool
    {
        // Si le plan n'existe pas ou n'a pas de période d'essai, on considère que l'essai n'est pas expiré
        if (! $this->plan || empty($this->plan->trial_days)) {
            return false;
        }

        // La date de début de l'essai est la date d'activation ou à défaut la date de création du tenant
        $start = $this->date_activation ?? $this->created_at;

        // Si la date d'expiration est explicitement définie, on l'utilise
        if ($this->date_expiration) {
            return $this->date_expiration->isPast();
        }

        // Sinon on calcule à partir de la date de début + durée de l'essai
        return $start->addDays($this->plan->trial_days)->isPast();
    }

    /**
     * Récupère la date de fin d'essai (ou null si pas d'essai).
     */
    public function getTrialEndsAtAttribute(): ?Carbon
    {
        if (! $this->plan || empty($this->plan->trial_days)) {
            return null;
        }

        $start = $this->date_activation ?? $this->created_at;

        return Carbon::parse($start)->addDays($this->plan->trial_days);
    }

    /**
     * Accesseur pour obtenir l'URL du logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return tenancy()->central(function () {

            $media = $this->getFirstMedia('tenant_avatar');

            return $media?->getUrl();
        });
    }
}
