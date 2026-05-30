<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    public function getConnectionName()
    {
        return config('tenancy.database.central_connection', 'central');
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'highlight',
        'price',
        'currency',
        'interval',
        'trial_days',
        'stripe_price_id',
        'stripe_product_id',
        'features',
        'limits',
        'sort_order',
        'is_active',
        'is_featured',
        'is_recommended',
        'badge',
        'badge_color',
        'button_text',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
        'features' => 'array',
        'limits' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_recommended' => 'boolean',
    ];

    protected $appends = [
        'formatted_price',
        'is_free',
        'has_trial',
        'features_list',
        'monthly_price',
        'savings',
        'badge_color_class',
        'stripe_checkout_url',
    ];

    // ==========================================
    // CONSTANTES
    // ==========================================

    const INTERVAL_MONTH = 'month';

    const INTERVAL_YEAR = 'year';

    const INTERVAL_LIFETIME = 'lifetime';

    // ==========================================
    // RELATIONS
    // ==========================================

    public function vendorRequests(): HasMany
    {
        return $this->hasMany(VendorRequest::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Prix formaté avec devise.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_free) {
            return 'Gratuit';
        }

        $price = number_format($this->price, 0, ',', ' ');
        $currency = $this->currency;

        $symbols = [
            'CDF' => 'FC',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$currency] ?? $currency;

        if ($this->interval === self::INTERVAL_YEAR) {
            return "{$price} {$symbol}/an";
        }

        if ($this->interval === self::INTERVAL_LIFETIME) {
            return "{$price} {$symbol} (à vie)";
        }

        return "{$price} {$symbol}/mois";
    }

    /**
     * Prix mensuel estimé (pour les plans annuels).
     */
    public function getMonthlyPriceAttribute(): ?float
    {
        if ($this->interval === self::INTERVAL_YEAR) {
            return round($this->price / 12, 2);
        }

        return $this->price;
    }

    /**
     * Économie réalisée pour un plan annuel vs mensuel.
     */
    public function getSavingsAttribute(): ?string
    {
        if ($this->interval !== self::INTERVAL_YEAR) {
            return null;
        }

        $monthlyEquivalent = $this->price / 12;
        $standardMonthly = $this->price * 0.1; // Estimation : ~10% plus cher en mensuel
        $monthlyPrice = $monthlyEquivalent + $standardMonthly;
        $yearlyStandard = $monthlyPrice * 12;
        $savings = $yearlyStandard - $this->price;
        $savingsPercent = round(($savings / $yearlyStandard) * 100);

        return "Économisez {$savingsPercent}%";
    }

    /**
     * Vérifie si le plan est gratuit.
     */
    public function getIsFreeAttribute(): bool
    {
        return $this->price == 0;
    }

    /**
     * Vérifie si le plan a une période d'essai.
     */
    public function getHasTrialAttribute(): bool
    {
        return $this->trial_days > 0;
    }

    /**
     * Liste des fonctionnalités formatées.
     */
    public function getFeaturesListAttribute(): array
    {
        if (! $this->features) {
            return [];
        }

        return array_map(function ($feature) {
            return [
                'text' => $feature,
                'included' => ! str_starts_with($feature, '❌'),
            ];
        }, $this->features);
    }

    /**
     * Limites du plan formatées.
     */
    public function getLimitsFormattedAttribute(): array
    {
        if (! $this->limits) {
            return [];
        }

        $defaults = [
            'products' => 'Illimité',
            'storage' => 'Illimité',
            'bandwidth' => 'Illimité',
            'staff_accounts' => '1',
            'sales_channels' => '1',
        ];

        return array_merge($defaults, $this->limits);
    }

    /**
     * Couleur du badge.
     */
    public function getBadgeColorClassAttribute(): string
    {
        return match ($this->badge_color) {
            'amber' => 'bg-amber-500',
            'green' => 'bg-green-500',
            'blue' => 'bg-blue-500',
            'purple' => 'bg-purple-500',
            'red' => 'bg-red-500',
            default => 'bg-amber-500',
        };
    }

    /**
     * URL de checkout pour ce plan.
     */
    public function getStripeCheckoutUrlAttribute(): ?string
    {
        if (! $this->stripe_price_id) {
            return null;
        }

        return route('vendor.payment.checkout', ['plan' => $this->slug]);
    }

    // ==========================================
    // MÉTHODES D'INSTANCE (REMPLACENT LES ANCIENS ACCESSORS)
    // ==========================================

    /**
     * Vérifier si le plan est gratuit (méthode, pas accesseur).
     */
    public function isFree(): bool
    {
        return $this->is_free;
    }

    /**
     * Vérifier si le plan a une période d'essai (méthode).
     */
    public function hasTrial(): bool
    {
        return $this->has_trial;
    }

    /**
     * Vérifier si le plan est éligible pour un nouvel utilisateur.
     */
    public function isEligibleFor(User $user): bool
    {
        // Un utilisateur qui a déjà ce plan ne peut pas le reprendre
        if ($user->plan_id === $this->id) {
            return false;
        }

        return $this->is_active;
    }

    /**
     * Comparer avec un autre plan.
     */
    public function compareWith(Plan $other): array
    {
        return [
            'price_diff' => $this->price - $other->price,
            'features_diff' => array_diff(
                $this->features ?? [],
                $other->features ?? []
            ),
            'limits_diff' => array_diff_assoc(
                $this->limits ?? [],
                $other->limits ?? []
            ),
        ];
    }

    /**
     * Vérifier si une fonctionnalité est incluse.
     */
    public function hasFeature(string $feature): bool
    {
        if (! $this->features) {
            return false;
        }

        return in_array($feature, $this->features);
    }

    /**
     * Obtenir la limite pour une ressource donnée.
     */
    public function getLimit(string $resource): mixed
    {
        if (! $this->limits) {
            return null;
        }

        return $this->limits[$resource] ?? null;
    }

    /**
     * Vérifier si une limite est dépassée.
     */
    public function isLimitReached(Tenant $tenant, string $resource, int $currentValue): bool
    {
        $limit = $this->getLimit($resource);

        if ($limit === null || $limit === 'Illimité') {
            return false;
        }

        return $currentValue >= (int) $limit;
    }

    /**
     * Vérifier si le plan est mis en avant.
     */
    public function isFeatured(): bool
    {
        return (bool) $this->is_featured;
    }

    /**
     * Vérifier si le plan est recommandé.
     */
    public function isRecommended(): bool
    {
        return (bool) $this->is_recommended;
    }

    /**
     * Obtenir le libellé de l'intervalle.
     */
    public function getIntervalLabel(): string
    {
        return match ($this->interval) {
            self::INTERVAL_MONTH => 'par mois',
            self::INTERVAL_YEAR => 'par an',
            self::INTERVAL_LIFETIME => 'à vie',
            default => (string) $this->interval,
        };
    }

    /**
     * Obtenir la devise symbolique.
     */
    public function getCurrencySymbol(): string
    {
        return match ($this->currency) {
            'CDF' => 'FC',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => (string) $this->currency,
        };
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('price', '>', 0);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function scopeInterval($query, string $interval)
    {
        return $query->where('interval', $interval);
    }
}
