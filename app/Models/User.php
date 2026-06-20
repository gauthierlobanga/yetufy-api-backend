<?php

namespace App\Models;

use App\Concerns\HasUserPreferences;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

// use Stancl\Tenancy\Contracts\SyncMaster;
// use Stancl\Tenancy\Database\Concerns\ResourceSyncing;

/**
 * @mixin HasRoles
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia, HasName, HasTenants // , SyncMaster , MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    use HasRoles, InteractsWithMedia;
    use HasUserPreferences;
    use HasUuids, SoftDeletes;
    // use ResourceSyncing;

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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_verifie',
        'is_active',
        'dernier_connexion',
        'preferences',
        // 'global_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'email_verifie' => 'boolean',
            'dernier_connexion' => 'datetime',
            'preferences' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Constantes pour les statuts
     */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public function adresses(): MorphMany
    {
        return $this->morphMany(Adresse::class, 'addressable');
    }

    public function adresseFacturation()
    {
        return $this->adresses()
            ->where('type', 'facturation')
            ->where('est_defaut', true)
            ->first();
    }

    public function adresseLivraison()
    {
        return $this->adresses()
            ->where('type', 'livraison')
            ->where('est_defaut', true)
            ->first();
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            Tenant::class,
            'user_tenant',
            'user_id',
            'tenant_id',
        )
            ->using(UserTenantPivot::class)
            ->withPivot('id', 'is_owner')
            ->withTimestamps();
    }

    // public function getTenantModelName(): string
    // {
    //     return TenantUser::class; // Le modèle utilisateur dans le tenant
    // }

    // public function getGlobalIdentifierKey()
    // {
    //     return $this->getAttribute($this->getGlobalIdentifierKeyName());
    // }

    // public function getGlobalIdentifierKeyName(): string
    // {
    //     return 'global_id';
    // }

    // public function getCentralModelName(): string
    // {
    //     return static::class;
    // }

    // public function getSyncedAttributeNames(): array
    // {
    //     return [
    //         'id',
    //         'name',
    //         'email',
    //         'password',
    //         'global_id',
    //     ];
    // }

    /**
     * Relations avec les autres modèles
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Media Library
     */
    public function registerMediaCollections(): void
    {

        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {

        $this->addMediaConversion('medium')
            ->format('webp')
            ->width(400)
            ->height(300)
            ->fit(Fit::Crop, 400, 300)
            ->optimize()
            ->nonQueued();

        // Conversion pour les avatars (carrés)
        $this->addMediaConversion('thumb')
            ->withResponsiveImages()
            ->format('webp')
            ->width(150)
            ->height(150)
            ->fit(Fit::Crop, 150, 150)
            ->optimize()
            ->performOnCollections('avatar');
    }

    /**
     * Filament Accessors
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->is_active && $this->hasRole('super_admin');
        }

        if ($panel->getId() === 'vendeur') {
            return $this->is_active && ($this->hasRole(['super_admin', 'Manager']) || $this->tenants()->exists());
        }

        return false;
    }

    /**
     * Accessors
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->hasMedia('avatar')) {
            return $this->getFirstMediaUrl('avatar', 'medium');
        }

        // Générer les initiales via ui-avatars
        $name = trim($this->name ?? $this->email);
        if (empty($name)) {
            $initials = '?';
        } else {
            $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
            $initials = collect($parts)
                ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
                ->take(2)
                ->implode('');
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($initials)
            .'&background=F59E0B&color=FFFFFF&size=128&bold=true';
    }

    public function getFullNameAttribute(): string
    {
        if ($this->prenom) {
            return $this->prenom.' '.$this->name;
        }

        return $this->name;
    }

    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?? $this->email);
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);

        return collect($parts)
            ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function getLastLoginAttribute(): ?string
    {
        return $this->dernier_connexion?->diffForHumans();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    public function scopeByRole($query, string $role)
    {
        return $query->whereHas('roles', fn ($q) => $q->where('name', $role));
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('prenom', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function getPermissionsListAttribute(): array
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    public function getRolesListAttribute(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    public function markEmailAsVerified(): bool
    {
        $attributes = [
            'email_verified_at' => $this->freshTimestamp(),
        ];

        if (Schema::hasColumn($this->getTable(), 'email_verifie')) {
            $attributes['email_verifie'] = true;
        }

        return $this->forceFill($attributes)->save();
    }

    public function markEmailAsUnverified(): bool
    {
        $attributes = [
            'email_verified_at' => null,
        ];

        if (Schema::hasColumn($this->getTable(), 'email_verifie')) {
            $attributes['email_verifie'] = false;
        }

        return $this->forceFill($attributes)->save();
    }

    public function updateLastLogin(): void
    {
        $this->dernier_connexion = now();
        $this->save();
    }

    public function toggleActive(): void
    {
        $this->is_active = ! $this->is_active;
        $this->save();
    }

    public function getPreference(string $key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, $value)
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->preferences = $preferences;
        $this->save();

        return $this;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->is_active)) {
                $user->is_active = true;
            }

            // if (empty($user->global_id)) {
            //     $user->global_id = (string) Str::orderedUuid();
            // }
        });
    }

    /**
     * Get the database connection name for the model.
     * Returns 'tenant' when tenancy is initialized, otherwise returns the default connection.
     */
    // public function getConnectionName(): string
    // {
    //     if (function_exists('tenancy') && tenancy()->initialized) {
    //         return 'tenant';
    //     }

    //     return parent::getConnectionName() ?? config('database.default');
    // }

    public function receivesBroadcastNotificationsOn($notification = null): string
    {
        $tenantId = null;

        if (
            $notification
            && method_exists($notification, 'tenantId')
            && filled($notificationTenantId = $notification->tenantId())
        ) {
            $tenantId = $notificationTenantId;
        }

        if (! filled($tenantId) && function_exists('tenant') && tenant()?->id) {
            $tenantId = tenant()->id;
        }

        if (filled($tenantId)) {
            return "tenant.{$tenantId}.users.{$this->getKey()}";
        }

        return str_replace('\\', '.', self::class).'.'.$this->getKey();
    }

    /**
     * Détermine si cet utilisateur peut impersonner d'autres utilisateurs
     */
    public function canImpersonate(): bool
    {
        return $this->hasRole('super_admin') && $this->is_active;
    }

    /**
     * Détermine si cet utilisateur peut être impersonné
     */
    public function canBeImpersonated(): bool
    {
        if ($this->hasRole('super_admin')) {
            return false;
        }

        if ($this->id === Auth::id()) {
            return false;
        }

        return true;
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->tenants();
    }

    public function paniers(): HasMany
    {
        return $this->hasMany(Panier::class);
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function avis(): HasMany
    {
        return $this->hasMany(AvisClient::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        if ($this->hasRole('super_admin')) {
            return Tenant::query()
                ->orderBy('raison_sociale')
                ->get();
        }

        return $this->tenants()
            ->orderBy('raison_sociale')
            ->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! $tenant instanceof Tenant) {
            return false;
        }

        if ($this->hasRole(['super_admin'])) {
            return true;
        }

        return $this->tenants()
            ->whereKey($tenant->getKey())
            ->exists();
    }

    /**
     * Méthodes métier
     */
    public function hasClient(): bool
    {
        return $this->client()->exists();
    }
}
