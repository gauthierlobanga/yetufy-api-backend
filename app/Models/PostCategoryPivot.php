<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PostCategoryPivot extends Pivot
{
    use HasUuids;

    protected $table = 'posts_categories_pivot';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'post_id',
        'category_id',
        'est_principale',
        'is_primary',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'est_principale' => 'boolean',
            'is_primary' => 'boolean',
            'order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Accessors
    public function getEstPrincipaleLabelAttribute(): string
    {
        return $this->est_principale ? 'Oui' : 'Non';
    }

    // Méthodes métier
    public function definirCommePrincipale(): void
    {
        // Retirer le statut principal des autres catégories pour ce post
        self::where('post_id', $this->post_id)
            ->update(['est_principale' => false]);

        $this->est_principale = true;
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
