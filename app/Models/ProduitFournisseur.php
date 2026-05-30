<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProduitFournisseur extends Pivot
{
    protected $table = 'produit_fournisseur';

    protected $fillable = [
        'produit_id',
        'fournisseur_id',
        'prix_achat_ht',
        'delai_approvisionnement_jours',
        'reference_fournisseur',
        'est_principal',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'prix_achat_ht' => 'decimal:2',
        'est_principal' => 'boolean',
    ];
}
