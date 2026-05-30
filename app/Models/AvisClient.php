<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AvisClient extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia;
    use SoftDeletes;

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

    protected $table = 'avis_clients';

    protected $fillable = [
        'client_id',
        'produit_id',
        'note',
        'commentaire',
        'reponse',
        'approuve',
        'date_avis',
    ];

    protected function casts(): array
    {
        return [
            'note' => 'integer',
            'approuve' => 'boolean',
            'date_avis' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->useDisk(config('media-library.disk_name', 'public'))
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->performOnCollections('photos')
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(5)
            ->performOnCollections('photos')
            ->nonQueued();
    }

    // Accessors
    public function getPhotosAttribute(): array
    {
        return $this->getMediaUrls('photos', 'medium');
    }

    public function getPhotosThumbAttribute(): array
    {
        return $this->getMediaUrls('photos', 'thumb');
    }

    public function getNoteLibelleAttribute(): string
    {
        return match ($this->note) {
            5 => 'Excellent',
            4 => 'Très bon',
            3 => 'Bon',
            2 => 'Moyen',
            1 => 'Mauvais',
            default => 'Non noté',
        };
    }

    // Méthodes utilitaires
    public function approuver(): void
    {
        $this->approuve = true;
        $this->save();
    }

    public function desapprouver(): void
    {
        $this->approuve = false;
        $this->save();
    }

    public function repondre(string $reponse): void
    {
        $this->reponse = $reponse;
        $this->save();
    }

    public function scopeApprouves($query)
    {
        return $query->where('approuve', true);
    }

    public function scopeNonApprouves($query)
    {
        return $query->where('approuve', false);
    }

    public function scopePourProduit($query, $produitId)
    {
        return $query->where('produit_id', $produitId);
    }

    public function scopeNoteMin($query, $note)
    {
        return $query->where('note', '>=', $note);
    }

    public function scopeRecents($query)
    {
        return $query->orderBy('date_avis', 'desc');
    }

    public function scopeUtiles($query)
    {
        return $query->orderBy('note', 'desc');
    }
}
