<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSend extends Model
{
    use HasFactory;
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

    protected $table = 'newsletter_sends';

    protected $fillable = [
        'campaign_id',
        'newsletter_id',
        'email',
        'status',
        'opened_at',
        'clicked_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    const STATUS_ENVOYE = 'envoye';

    const STATUS_OUVERT = 'ouvert';

    const STATUS_CLIQUE = 'clique';

    const STATUS_ERREUR = 'erreur';

    const STATUS_DESABONNE = 'desabonne';

    // Relations
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'campaign_id');
    }

    public function newsletter(): BelongsTo
    {
        return $this->belongsTo(Newsletter::class);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ENVOYE => 'Envoyé',
            self::STATUS_OUVERT => 'Ouvert',
            self::STATUS_CLIQUE => 'Cliqué',
            self::STATUS_ERREUR => 'Erreur',
            self::STATUS_DESABONNE => 'Désabonné',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ENVOYE => 'primary',
            self::STATUS_OUVERT => 'warning',
            self::STATUS_CLIQUE => 'success',
            self::STATUS_ERREUR => 'danger',
            self::STATUS_DESABONNE => 'gray',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeOuverts($query)
    {
        return $query->whereNotNull('opened_at');
    }

    public function scopeCliques($query)
    {
        return $query->whereNotNull('clicked_at');
    }

    // Méthodes métier
    public function marquerOuvert(): void
    {
        if (! $this->opened_at) {
            $this->status = self::STATUS_OUVERT;
            $this->opened_at = now();
            $this->save();

            $this->campaign->incrementerOuverts();
        }
    }

    public function marquerClique(): void
    {
        if (! $this->clicked_at) {
            $this->status = self::STATUS_CLIQUE;
            $this->clicked_at = now();
            $this->save();

            $this->campaign->incrementerClics();
        }
    }

    public function marquerDesabonne(): void
    {
        $this->status = self::STATUS_DESABONNE;
        $this->save();

        $this->newsletter->desactiver();
        $this->campaign->incrementerDesabonnements();
    }
}
