<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasComments, HasUuids;

    /** @use HasFactory<CommentFactory> */
    use HasFactory, SoftDeletes;

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

    protected $table = 'comments';

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'parent_id',
        'content',
        'metadata',
        'status',
        'likes_count',
        'dislikes_count',
        'replies_count',
        'reports_count',
        'ip_address',
        'user_agent',
        'approved_at',
        'edited_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'likes_count' => 'integer',
        'dislikes_count' => 'integer',
        'replies_count' => 'integer',
        'reports_count' => 'integer',
        'approved_at' => 'datetime',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_SPAM = 'spam';

    const STATUS_TRASHED = 'trashed';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_SPAM => 'Spam',
            self::STATUS_TRASHED => 'Corbeille',
        ];
    }

    // ========== RELATIONS ==========

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'commentable_id')
            ->where('commentable_type', Post::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function approvedReplies(): HasMany
    {
        return $this->replies()->where('status', self::STATUS_APPROVED);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(CommentMention::class);
    }

    public function mentionedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comment_mentions', 'comment_id', 'user_id')
            ->withTimestamps();
    }

    // ========== ACCESSORS ==========

    public function getContentHtmlAttribute(): string
    {
        // Convertir les mentions en liens
        $content = preg_replace_callback('/@(\w+)/', function ($matches) {
            $user = User::where('name', $matches[1])->first();
            if ($user) {
                return '<a href="/profile/'.$user->id.'" class="text-primary hover:underline">@'.$matches[1].'</a>';
            }

            return '@'.$matches[1];
        }, e($this->content));

        // Convertir les URLs en liens
        $content = preg_replace(
            '/(http?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">$1</a>',
            $content
        );

        // Convertir les retours à la ligne en <br>
        $content = nl2br($content);

        return $content;
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content_html), 150);
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getIsSpamAttribute(): bool
    {
        return $this->status === self::STATUS_SPAM;
    }

    public function getNestedRepliesAttribute(): array
    {
        return $this->replies()
            ->with('user')
            ->where('status', self::STATUS_APPROVED)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    // ========== SCOPES ==========

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSpam($query)
    {
        return $query->where('status', self::STATUS_SPAM);
    }

    public function scopeForModel($query, Model $model)
    {
        return $query->where('commentable_type', get_class($model))
            ->where('commentable_id', $model->getKey());
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeMostLiked($query)
    {
        return $query->orderBy('likes_count', 'desc');
    }

    // ========== MÉTHODES MÉTIER ==========

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_at = now();
        $this->save();

        // Notifier l'auteur du commentaire
        // event(new CommentApproved($this));
    }

    public function markAsSpam(): void
    {
        $this->status = self::STATUS_SPAM;
        $this->save();
    }

    public function trash(): void
    {
        $this->status = self::STATUS_TRASHED;
        $this->save();
    }

    public function edit(string $newContent): void
    {
        $this->content = $newContent;
        $this->edited_at = now();
        $this->save();

        // Détecter les mentions
        $this->detectMentions();
    }

    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    public function decrementLikes(): void
    {
        $this->decrement('likes_count');
    }

    public function incrementDislikes(): void
    {
        $this->increment('dislikes_count');
    }

    public function decrementDislikes(): void
    {
        $this->decrement('dislikes_count');
    }

    public function incrementReplies(): void
    {
        $this->increment('replies_count');
    }

    public function decrementReplies(): void
    {
        $this->decrement('replies_count');
    }

    public function hasUserLiked(User $user): bool
    {
        return $this->likes()
            ->where('user_id', $user->id)
            ->where('type', 'like')
            ->exists();
    }

    public function hasUserDisliked(User $user): bool
    {
        return $this->likes()
            ->where('user_id', $user->id)
            ->where('type', 'dislike')
            ->exists();
    }

    public function toggleLike(User $user): array
    {
        $existing = $this->likes()->where('user_id', $user->id)->first();

        if ($existing) {
            if ($existing->type === 'like') {
                $existing->delete();
                $this->decrementLikes();

                return ['action' => 'removed', 'type' => 'like'];
            } else {
                $existing->delete();
                $this->decrementDislikes();

                $this->likes()->create([
                    'user_id' => $user->id,
                    'type' => 'like',
                ]);
                $this->incrementLikes();

                return ['action' => 'changed', 'from' => 'dislike', 'to' => 'like'];
            }
        } else {
            $this->likes()->create([
                'user_id' => $user->id,
                'type' => 'like',
            ]);
            $this->incrementLikes();

            return ['action' => 'added', 'type' => 'like'];
        }
    }

    public function toggleDislike(User $user): array
    {
        $existing = $this->likes()->where('user_id', $user->id)->first();

        if ($existing) {
            if ($existing->type === 'dislike') {
                $existing->delete();
                $this->decrementDislikes();

                return ['action' => 'removed', 'type' => 'dislike'];
            } else {
                $existing->delete();
                $this->decrementLikes();

                $this->likes()->create([
                    'user_id' => $user->id,
                    'type' => 'dislike',
                ]);
                $this->incrementDislikes();

                return ['action' => 'changed', 'from' => 'like', 'to' => 'dislike'];
            }
        } else {
            $this->likes()->create([
                'user_id' => $user->id,
                'type' => 'dislike',
            ]);
            $this->incrementDislikes();

            return ['action' => 'added', 'type' => 'dislike'];
        }
    }

    public function detectMentions(): void
    {
        preg_match_all('/@(\w+)/', $this->content, $matches);
        $usernames = $matches[1];

        $users = User::whereIn('name', $usernames)->get();

        $this->mentions()->delete();

        foreach ($users as $user) {
            $this->mentions()->create([
                'user_id' => $user->id,
            ]);

            // Notifier l'utilisateur mentionné
            // event(new UserMentioned($this, $user));
        }
    }

    public function report(User $user, string $reason, ?string $details = null): CommentReport
    {
        $report = $this->reports()->create([
            'user_id' => $user->id,
            'reason' => $reason,
            'details' => $details,
        ]);

        $this->increment('reports_count');

        return $report;
    }

    // ========== CRÉATION ==========

    public static function createComment(
        Model $model,
        User $user,
        string $content,
        ?Comment $parent = null
    ): self {
        $comment = self::create([
            'commentable_type' => get_class($model),
            'commentable_id' => $model->getKey(),
            'user_id' => $user->id,
            'parent_id' => $parent?->id,
            'content' => $content,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => config('comments.auto_approve', false) ? self::STATUS_APPROVED : self::STATUS_PENDING,
        ]);

        // Détecter les mentions
        $comment->detectMentions();

        // Incrémenter le compteur de réponses sur le parent
        if ($parent) {
            $parent->incrementReplies();
        }

        return $comment;
    }

    // ========== BOOT ==========

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($comment) {
            // Supprimer les likes associés
            $comment->likes()->delete();

            // Supprimer les signalements associés
            $comment->reports()->delete();

            // Supprimer les mentions associées
            $comment->mentions()->delete();

            // Mettre à jour le compteur du parent
            if ($comment->parent_id) {
                // Utiliser query() pour éviter l'appel de méthode
                self::where('id', $comment->parent_id)->decrement('replies_count');
            }
        });
    }
}
