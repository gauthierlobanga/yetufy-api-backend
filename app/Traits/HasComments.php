<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc');
    }

    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvedComments(): MorphMany
    {
        return $this->comments()->where('status', Comment::STATUS_APPROVED);
    }

    public function getCommentsCountAttribute(): int
    {
        return $this->approvedComments()->count();
    }

    public function addComment(User $user, string $content, ?Comment $parent = null): Comment
    {
        return Comment::createComment($this, $user, $content, $parent);
    }
}
