<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
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

    protected $table = 'notification_commandes';

    protected $fillable = [
        'commande_id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'sujet',
        'contenu',
        'statut',
        'metadata',
        'date_envoi',
        'date_lecture',
    ];

    protected $casts = [
        'metadata' => 'array',
        'contenu' => 'array',
        'date_envoi' => 'datetime',
        'date_lecture' => 'datetime',
    ];

    const TYPE_EMAIL = 'email';

    const TYPE_SMS = 'sms';

    const TYPE_PUSH = 'push';

    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_ENVOYE = 'envoye';

    const STATUT_ECHEC = 'echec';

    /**
     * Relations
     */
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeNonEnvoyees($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    public function scopePourClient($query, Client $client)
    {
        return $query->where('notifiable_type', Client::class)
            ->where('notifiable_id', $client->id);
    }

    public function scopePourUtilisateur($query, User $user)
    {
        return $query->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id);
    }
}
