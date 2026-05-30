<?php

// app/Models/MouvementStock.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MouvementStock extends Model
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
        'produit_id',
        'entrepot_id',
        'inventaire_id',
        'type',
        'quantite',
        'reference',
        'notes',
        'metadata',
        'date_mouvement',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'metadata' => 'array',
        'date_mouvement' => 'datetime',
    ];

    const TYPE_ENTREE = 'entree';

    const TYPE_SORTIE = 'sortie';

    const TYPE_AJUSTEMENT = 'ajustement';

    const TYPE_TRANSFERT = 'transfert';

    /**
     * Relations
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function entrepot(): BelongsTo
    {
        return $this->belongsTo(Entrepot::class);
    }

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }

    /**
     * Méthodes métier
     */
    public static function enregistrerEntree(Produit $produit, int $quantite, string $reference, ?Entrepot $entrepot = null): self
    {
        $entrepot = $entrepot ?? Entrepot::where('est_principal', true)->first();

        return self::create([
            'produit_id' => $produit->id,
            'entrepot_id' => $entrepot?->id,
            'type' => self::TYPE_ENTREE,
            'quantite' => $quantite,
            'reference' => $reference,
            'date_mouvement' => now(),
        ]);
    }

    public static function enregistrerSortie(Produit $produit, int $quantite, string $reference, ?Entrepot $entrepot = null): self
    {
        $entrepot = $entrepot ?? Entrepot::where('est_principal', true)->first();

        return self::create([
            'produit_id' => $produit->id,
            'entrepot_id' => $entrepot?->id,
            'type' => self::TYPE_SORTIE,
            'quantite' => -$quantite,
            'reference' => $reference,
            'date_mouvement' => now(),
        ]);
    }
}
