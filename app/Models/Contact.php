<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contact extends Model
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

    protected $table = 'contacts';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'sujet',
        'message',
        'attachments',
        'status',
        'priorite',
        'categorie',
        'ip_address',
        'user_agent',
        'reponse',
        'repondu_par',
        'lu_at',
        'repondu_at',
        'metadata',
    ];

    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'lu_at' => 'datetime',
        'repondu_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Constantes
    const STATUS_EN_ATTENTE = 'en_attente';

    const STATUS_LU = 'lu';

    const STATUS_REPONDU = 'repondu';

    const STATUS_ARCHIVE = 'archive';

    const STATUS_SPAM = 'spam';

    const PRIORITE_BASSE = 'basse';

    const PRIORITE_MOYENNE = 'moyenne';

    const PRIORITE_HAUTE = 'haute';

    const PRIORITE_URGENTE = 'urgente';

    const CATEGORIE_GENERAL = 'general';

    const CATEGORIE_COMMERCIAL = 'commercial';

    const CATEGORIE_TECHNIQUE = 'technique';

    const CATEGORIE_SUPPORT = 'support';

    const CATEGORIE_RECLAMATION = 'reclamation';

    private const DEFAULT_PRIORITY_BY_CATEGORY = [
        self::CATEGORIE_GENERAL => self::PRIORITE_MOYENNE,
        self::CATEGORIE_COMMERCIAL => self::PRIORITE_MOYENNE,
        self::CATEGORIE_TECHNIQUE => self::PRIORITE_HAUTE,
        self::CATEGORIE_SUPPORT => self::PRIORITE_HAUTE,
        self::CATEGORIE_RECLAMATION => self::PRIORITE_URGENTE,
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_EN_ATTENTE => 'En attente',
            self::STATUS_LU => 'Lu',
            self::STATUS_REPONDU => 'Répondu',
            self::STATUS_ARCHIVE => 'Archivé',
            self::STATUS_SPAM => 'Spam',
        ];
    }

    public static function getPriorites(): array
    {
        return [
            self::PRIORITE_BASSE => 'Basse',
            self::PRIORITE_MOYENNE => 'Moyenne',
            self::PRIORITE_HAUTE => 'Haute',
            self::PRIORITE_URGENTE => 'Urgente',
        ];
    }

    public static function getCategories(): array
    {
        return [
            self::CATEGORIE_GENERAL => 'Général',
            self::CATEGORIE_COMMERCIAL => 'Commercial',
            self::CATEGORIE_TECHNIQUE => 'Technique',
            self::CATEGORIE_SUPPORT => 'Support',
            self::CATEGORIE_RECLAMATION => 'Réclamation',
        ];
    }

    public static function inferPriority(string $categorie, ?string $message = null): string
    {
        if (self::isUrgentMessage($message)) {
            return self::PRIORITE_URGENTE;
        }

        return self::DEFAULT_PRIORITY_BY_CATEGORY[$categorie] ?? self::PRIORITE_MOYENNE;
    }

    public static function isUrgentMessage(?string $message): bool
    {
        if (blank($message)) {
            return false;
        }

        $normalizedMessage = Str::lower(Str::ascii($message));
        $urgentKeywords = [
            'urgent',
            'urgence',
            'asap',
            'immediat',
            'rapidement',
            'critique',
            'bloquant',
            'panne',
        ];

        foreach ($urgentKeywords as $keyword) {
            if (Str::contains($normalizedMessage, $keyword)) {
                return true;
            }
        }

        return false;
    }

    // Relations
    public function reponduPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'repondu_par');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_EN_ATTENTE => 'warning',
            self::STATUS_LU => 'info',
            self::STATUS_REPONDU => 'success',
            self::STATUS_ARCHIVE => 'gray',
            self::STATUS_SPAM => 'danger',
            default => 'gray',
        };
    }

    public function getPrioriteLabelAttribute(): string
    {
        return self::getPriorites()[$this->priorite] ?? $this->priorite;
    }

    public function getPrioriteColorAttribute(): string
    {
        return match ($this->priorite) {
            self::PRIORITE_BASSE => 'gray',
            self::PRIORITE_MOYENNE => 'info',
            self::PRIORITE_HAUTE => 'warning',
            self::PRIORITE_URGENTE => 'danger',
            default => 'gray',
        };
    }

    public function getCategorieLabelAttribute(): string
    {
        return self::getCategories()[$this->categorie] ?? $this->categorie;
    }

    public function getCategorieColorAttribute(): string
    {
        return match ($this->categorie) {
            self::CATEGORIE_GENERAL => 'gray',
            self::CATEGORIE_COMMERCIAL => 'primary',
            self::CATEGORIE_TECHNIQUE => 'info',
            self::CATEGORIE_SUPPORT => 'warning',
            self::CATEGORIE_RECLAMATION => 'danger',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('status', self::STATUS_EN_ATTENTE);
    }

    public function scopeNonTraites($query)
    {
        return $query->whereIn('status', [self::STATUS_EN_ATTENTE, self::STATUS_LU]);
    }

    public function scopeUrgents($query)
    {
        return $query->where('priorite', self::PRIORITE_URGENTE)
            ->whereIn('status', [self::STATUS_EN_ATTENTE, self::STATUS_LU]);
    }

    public function scopeParCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    // Méthodes métier
    public function marquerLu(): void
    {
        if (! $this->lu_at) {
            $this->status = self::STATUS_LU;
            $this->lu_at = now();
            $this->save();
        }
    }

    public function marquerRepondu(User $user, string $reponse): void
    {
        $this->status = self::STATUS_REPONDU;
        $this->reponse = $reponse;
        $this->repondu_par = $user->id;
        $this->repondu_at = now();
        $this->save();
    }

    public function archiver(): void
    {
        $this->status = self::STATUS_ARCHIVE;
        $this->save();
    }

    public function marquerSpam(): void
    {
        $this->status = self::STATUS_SPAM;
        $this->save();
    }
}
