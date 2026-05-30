<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionClient extends Pivot
{
    use SoftDeletes;

    protected $table = 'promotion_client';

    protected $fillable = [
        'promotion_id',
        'client_id',
        'utilisations',
        'utilisations_max',
        'premiere_utilisation',
        'derniere_utilisation',
        'est_actif',
        'notes',
    ];

    protected $casts = [
        'utilisations' => 'integer',
        'utilisations_max' => 'integer',
        'premiere_utilisation' => 'datetime',
        'derniere_utilisation' => 'datetime',
        'est_actif' => 'boolean',
    ];

    protected $attributes = [
        'utilisations' => 0,
        'est_actif' => true,
    ];

    // Relations
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Accessors
    public function getUtilisationsRestantesAttribute(): ?int
    {
        if (! $this->utilisations_max) {
            return null; // Illimité
        }

        return max(0, $this->utilisations_max - $this->utilisations);
    }

    public function getPeutUtiliserAttribute(): bool
    {
        // Vérifier si la promotion est active
        if (! $this->est_actif || ! $this->promotion?->est_active) {
            return false;
        }

        // Vérifier le nombre d'utilisations
        if ($this->utilisations_max && $this->utilisations >= $this->utilisations_max) {
            return false;
        }

        // Vérifier si le client est actif
        if ($this->client && ! $this->client->est_actif) {
            return false;
        }

        return true;
    }

    public function getTauxUtilisationAttribute(): float
    {
        if (! $this->utilisations_max) {
            return 0;
        }

        return round(($this->utilisations / $this->utilisations_max) * 100, 2);
    }

    public function getPremiereUtilisationLabelAttribute(): string
    {
        return $this->premiere_utilisation?->format('d/m/Y H:i') ?? 'Jamais';
    }

    public function getDerniereUtilisationLabelAttribute(): string
    {
        return $this->derniere_utilisation?->format('d/m/Y H:i') ?? 'Jamais';
    }

    // Méthodes métier
    public function incrementerUtilisation(): void
    {
        $this->utilisations++;

        if (! $this->premiere_utilisation) {
            $this->premiere_utilisation = now();
        }

        $this->derniere_utilisation = now();
        $this->save();
    }

    public function resetUtilisations(): void
    {
        $this->utilisations = 0;
        $this->premiere_utilisation = null;
        $this->derniere_utilisation = null;
        $this->save();
    }

    public function activer(): void
    {
        $this->est_actif = true;
        $this->save();
    }

    public function desactiver(): void
    {
        $this->est_actif = false;
        $this->save();
    }

    public function peutUtiliserPourClient(Client $client): bool
    {
        return $this->client_id === $client->id && $this->peut_utiliser;
    }

    // Scopes
    public function scopeUtilisables($query)
    {
        return $query->where('est_actif', true)
            ->where(function ($q) {
                $q->whereNull('utilisations_max')
                    ->orWhereColumn('utilisations', '<', 'utilisations_max');
            });
    }

    public function scopeActifs($query)
    {
        return $query->where('est_actif', true);
    }

    public function scopeParClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeParPromotion($query, $promotionId)
    {
        return $query->where('promotion_id', $promotionId);
    }

    public function scopeAvecUtilisations($query)
    {
        return $query->where('utilisations', '>', 0);
    }
}
