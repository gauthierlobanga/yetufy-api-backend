<?php

namespace App\Providers;

use App\Models\Comment;
use App\Observers\CommentObserver;
use Illuminate\Support\ServiceProvider;

class CommentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Comment::observe(CommentObserver::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/comments.php', 'comments'
        );
    }
}
