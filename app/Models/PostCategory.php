<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Table('posts_categories')]
class PostCategory extends Model
{
    use SoftDeletes;

    protected $table = 'posts_categories';

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
        'parent_id',
        'nom',
        'slug',
        'description',
        'color',
        'metadata',
        'ordre',
        'est_active',
        'est_visible_dans_menu',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'meta_keywords' => 'array',
            'ordre' => 'integer',
            'est_active' => 'boolean',
            'est_visible_dans_menu' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(PostCategory::class, 'parent_id')->orderBy('ordre');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'posts_categories_pivot',
            'category_id',
            'post_id'
        )
            ->using(PostCategoryPivot::class)
            ->withPivot('est_principale', 'ordre')
            ->withTimestamps();
    }

    public function postsPublies()
    {
        return $this->posts()->published();
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        return route('tenant.blog.category', $this->slug);
    }

    public function getUrlCategoryAttribute(): string
    {
        return route('tenant.blog.category', $this->slug);
    }

    public function getFullPathAttribute(): string
    {
        $path = [$this->nom];
        $parent = $this->parent;
        while ($parent) {
            array_unshift($path, $parent->nom);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    public function getSlugPathAttribute(): string
    {
        $path = [$this->slug];
        $parent = $this->parent;
        while ($parent) {
            array_unshift($path, $parent->slug);
            $parent = $parent->parent;
        }

        return implode('/', $path);
    }

    public function getCountPostsAttribute(): int
    {
        return $this->posts()->published()->count();
    }

    public function getMetaTitleAttribute($value): ?string
    {
        if ($value) {
            return $value ?? $this->nom;
        }

        return null;
    }

    public function getMetaDescriptionAttribute($value): ?string
    {
        if ($value) {
            return $value ?? $this->description;
        }

        return null;
    }

    /**
     * Scope pour trier par nom (compatible PostgreSQL)
     */

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('est_active', true);
    }

    public function scopeVisiblesDansMenu($query)
    {
        return $query->where('est_visible_dans_menu', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeEnfants($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeOrdonnes($query)
    {
        return $query->orderBy('ordre');
    }

    public function scopeRecherche($query, string $term)
    {
        return $query->where('nom', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%");
    }

    // Méthodes métier
    public function hasChildren(): bool
    {
        return $this->enfants()->exists();
    }

    public function hasParent(): bool
    {
        return ! is_null($this->parent_id);
    }

    public function getTreeIds(): array
    {
        $ids = [$this->id];
        foreach ($this->enfants as $enfant) {
            $ids = array_merge($ids, $enfant->getTreeIds());
        }

        return $ids;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $current = $this;
        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->id,
                'nom' => $current->nom,
                'slug' => $current->slug,
                'url' => $current->url,
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    public function incrementOrdre(): void
    {
        $this->increment('ordre');
    }

    public function decrementOrdre(): void
    {
        $this->decrement('ordre');
    }

    public function activer(): void
    {
        $this->est_active = true;
        $this->save();
    }

    public function desactiver(): void
    {
        $this->est_active = false;
        $this->save();
    }

    public function rendreVisibleDansMenu(): void
    {
        $this->est_visible_dans_menu = true;
        $this->save();
    }

    public function rendreInvisibleDansMenu(): void
    {
        $this->est_visible_dans_menu = false;
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->nom);
            }
        });

        static::deleting(function ($category) {
            if ($category->hasChildren()) {
                $category->enfants()->update(['parent_id' => $category->parent_id]);
            }
        });
    }
}
