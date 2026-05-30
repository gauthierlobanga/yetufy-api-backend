<?php

// app/Models/LigneRetour.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LigneRetour extends Model
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

    protected $fillable = [
        'retour_id',
        'ligne_commande_id',
        'quantite',
        'montant',
        'etat',
        'commentaire',
        'metadata',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'montant' => 'decimal:2',
        'metadata' => 'array',
    ];

    const ETAT_CONFORME = 'conforme';

    const ETAT_DEFECTUEUX = 'defectueux';

    const ETAT_ENDOMAGE = 'endommage';

    const ETAT_INCOMPLET = 'incomplet';

    public function retour(): BelongsTo
    {
        return $this->belongsTo(Retour::class);
    }

    public function ligneCommande(): BelongsTo
    {
        return $this->belongsTo(LigneCommande::class);
    }

    public function getEtatLabelAttribute(): string
    {
        return match ($this->etat) {
            self::ETAT_CONFORME => 'Conforme',
            self::ETAT_DEFECTUEUX => 'Défectueux',
            self::ETAT_ENDOMAGE => 'Endommagé',
            self::ETAT_INCOMPLET => 'Incomplet',
            default => $this->etat,
        };
    }
}
