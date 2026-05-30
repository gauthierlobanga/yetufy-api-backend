<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
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
        'client_id',
        'nom',
        'est_publique',
    ];

    protected $casts = [
        'est_publique' => 'boolean',
    ];

    protected $attributes = [
        'est_publique' => false,
    ];

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Produit::class, 'wishlist_items', 'wishlist_id', 'produit_id')
            ->withPivot('quantite', 'added_at')
            ->withTimestamps();
    }

    // Accessors
    public function getNomAttribute($value): string
    {
        return $value ?? 'Ma liste de souhaits';
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    public function getShareUrlAttribute(): ?string
    {
        if ($this->est_publique) {
            // return route('wishlist.shared', $this->id);
        }

        return null;
    }

    // Méthodes métier
    public function addProduct(Produit $produit, int $quantite = 1, ?string $note = null): WishlistItem
    {
        $item = $this->items()->where('produit_id', $produit->id)->first();

        if ($item) {
            $item->quantite = $quantite;
            if ($note) {
                $item->note = $note;
            }
            $item->save();

            return $item;
        }

        return $this->items()->create([
            'produit_id' => $produit->id,
            'quantite' => $quantite,
            'note' => $note,
            'added_at' => now(),
        ]);
    }

    public function removeProduct(Produit $produit): bool
    {
        return $this->items()->where('produit_id', $produit->id)->delete() > 0;
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    public function makePublic(): void
    {
        $this->est_publique = true;
        $this->save();
    }

    public function makePrivate(): void
    {
        $this->est_publique = false;
        $this->save();
    }
}
