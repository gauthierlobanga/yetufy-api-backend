<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Marketing extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia,SoftDeletes;

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

    protected $table = 'campagne_marketings';

    protected $fillable = [
        'nom',
        'type',
        'canal',
        'statut',
        'cible',
        'date_debut',
        'date_fin',
        'budget',
        'statistiques',
    ];

    protected function casts(): array
    {
        return [
            'cible' => 'array',
            'statistiques' => 'array',
            'budget' => 'decimal:2',
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Constantes
    public const string TYPE_NEWSLETTER = 'newsletter';

    public const string TYPE_PROMOTION = 'promotion';

    public const string TYPE_SAISONNIERE = 'saisonniere';

    public const string TYPE_RELANCE = 'relance';

    public const string CANAL_EMAIL = 'email';

    public const string CANAL_SMS = 'sms';

    public const string CANAL_RESEAUX = 'reseaux';

    public const string CANAL_PUSH = 'push';

    public const string STATUT_PLANIFIEE = 'planifiee';

    public const string STATUT_ACTIVE = 'active';

    public const string STATUT_TERMINEE = 'terminee';

    public const string STATUT_ANNULEE = 'annulee';

    public static function getTypes(): array
    {
        return [
            self::TYPE_NEWSLETTER,
            self::TYPE_PROMOTION,
            self::TYPE_SAISONNIERE,
            self::TYPE_RELANCE,
        ];
    }

    public static function getCanaux(): array
    {
        return [
            self::CANAL_EMAIL,
            self::CANAL_SMS,
            self::CANAL_RESEAUX,
            self::CANAL_PUSH,
        ];
    }

    public static function getStatuts(): array
    {
        return [
            self::STATUT_PLANIFIEE,
            self::STATUT_ACTIVE,
            self::STATUT_TERMINEE,
            self::STATUT_ANNULEE,
        ];
    }

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'campagne_promotion');
    }

    // Media Library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->useDisk(config('media-library.disk_name', 'public'))
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);

        $this->addMediaCollection('documents')
            ->useDisk(config('media-library.disk_name', 'public'))
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'text/csv',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->performOnCollections('image')
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(5)
            ->performOnCollections('image')
            ->nonQueued();
    }

    // Accessors
    public function getImageAttribute(): ?string
    {
        return $this->getFirstMediaUrl('image', 'medium');
    }

    public function getImageThumbAttribute(): ?string
    {
        return $this->getFirstMediaUrl('image', 'thumb');
    }

    public function getEstEnCoursAttribute(): bool
    {
        $now = now();

        return $this->statut === self::STATUT_ACTIVE &&
            $this->date_debut <= $now &&
            (! $this->date_fin || $this->date_fin >= $now);
    }

    public function getTauxOuvertureAttribute(): ?float
    {
        return $this->statistiques['taux_ouverture'] ?? null;
    }

    public function getTauxConversionAttribute(): ?float
    {
        return $this->statistiques['taux_conversion'] ?? null;
    }

    // Méthodes utilitaires
    public function lancer(): void
    {
        $this->statut = self::STATUT_ACTIVE;
        $this->date_debut = $this->date_debut ?? now();
        $this->save();
    }

    public function terminer(): void
    {
        $this->statut = self::STATUT_TERMINEE;
        $this->date_fin = now();
        $this->save();
    }

    public function annuler(): void
    {
        $this->statut = self::STATUT_ANNULEE;
        $this->save();
    }

    public function mettreAJourStatistiques(array $stats): void
    {
        $this->statistiques = array_merge($this->statistiques ?? [], $stats);
        $this->save();
    }
}
