<?php

// app/Models/CategoriePostPivot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductCategoryPivot extends Pivot
{
    use HasUuids;

    protected $table = 'produit_categorie_pivot';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'produit_id',
        'category_id',
        'is_primary',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    // Accessors
    public function getEstPrincipaleLabelAttribute(): string
    {
        return $this->is_primary ? 'Oui' : 'Non';
    }

    // Méthodes métier
    public function definirCommePrincipale(): void
    {
        // Retirer le statut principal des autres catégories pour ce post
        self::where('produit_id', $this->produit_id)
            ->update(['is_primary' => false]);

        $this->is_primary = true;
        $this->save();
    }

    public function incrementerOrdre(): void
    {
        $this->increment('order');
    }

    public function decrementerOrdre(): void
    {
        $this->decrement('order');
    }
}
