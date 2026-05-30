<?php

namespace App\Observers;

use App\Models\Comment;
use Illuminate\Support\Facades\Cache;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        Cache::tags(['comments'])->flush();

        // Détection de spam
        if (config('comments.spam.enabled')) {
            $this->checkForSpam($comment);
        }

        // Filtre de profanité
        if (config('comments.profanity.enabled')) {
            $this->filterProfanity($comment);
        }
    }

    public function updated(Comment $comment): void
    {
        Cache::tags(['comments'])->flush();
    }

    public function deleted(Comment $comment): void
    {
        Cache::tags(['comments'])->flush();
    }

    private function checkForSpam(Comment $comment): void
    {
        $content = strtolower($comment->content);

        // Vérifier les mots-clés de spam
        foreach (config('comments.spam.keywords', []) as $keyword) {
            if (str_contains($content, $keyword)) {
                $comment->markAsSpam();

                return;
            }
        }

        // Vérifier le nombre de liens
        $linkCount = preg_match_all('/http?:\/\//', $content);
        if ($linkCount > config('comments.spam.max_links', 2)) {
            $comment->markAsSpam();
        }
    }

    private function filterProfanity(Comment $comment): void
    {
        $content = $comment->content;
        $words = config('comments.profanity.words', []);
        $replacement = config('comments.profanity.replacement', '***');

        foreach ($words as $word) {
            $content = str_ireplace($word, $replacement, $content);
        }

        if ($content !== $comment->content) {
            $comment->content = $content;
            $comment->save();
        }
    }
}
