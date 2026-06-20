<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Retourne toutes les données du tableau de bord au format JSON.
     */
    public function adminDashboardIndex(Request $request): JsonResponse
    {
        if (! $this->hasDashboardSchema()) {
            return response()->json($this->emptyDashboardPayload($request));
        }

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super_admin');
        $driver = DB::connection()->getDriverName();

        // --- Récupération des posts paginés ---
        $query = Post::with(['user', 'categories', 'media', 'tags']);
        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');
        $this->applyFilters($query, $request, $isSuperAdmin, $user);
        $paginatedPosts = $query->paginate($request->per_page ?? 10);

        $posts = [
            'data' => PostResource::collection($paginatedPosts->items())->toArray($request),
            'current_page' => $paginatedPosts->currentPage(),
            'last_page' => $paginatedPosts->lastPage(),
            'from' => $paginatedPosts->firstItem(),
            'to' => $paginatedPosts->lastItem(),
            'total' => $paginatedPosts->total(),
            'per_page' => $paginatedPosts->perPage(),
        ];

        // --- Appel des méthodes de calcul (identiques à l'original) ---
        $stats = $this->computeStats($request, $isSuperAdmin, $user);
        $chartStats = $this->computeChartStats($request, $isSuperAdmin, $user, $driver);
        $categoriesStats = $this->computeCategoriesStats($request, $isSuperAdmin, $user);
        $postsStatusStats = $this->computePostsStatusStats($request, $isSuperAdmin, $user);
        $topPosts = $this->computeTopPosts($request, $isSuperAdmin, $user);
        $topAuthors = $isSuperAdmin ? $this->computeTopAuthors($request, $user) : [];
        $engagementStats = $this->computeEngagementStats($request, $isSuperAdmin, $user);
        $scheduledPosts = $this->computeScheduledPosts($request, $isSuperAdmin, $user);
        $weeklyActivity = $this->computeWeeklyActivity($request, $isSuperAdmin, $user, $driver);
        $monthlyPostsStats = $this->computeMonthlyStats($request, $isSuperAdmin, $user, $driver);
        $hourlyPostsStats = $this->computeHourlyStats($request, $isSuperAdmin, $user, $driver);
        $categoryPerformance = $this->computeCategoryPerformance($request, $isSuperAdmin, $user);
        $topTags = $isSuperAdmin ? $this->computeTopTags($request, $user) : [];

        $authors = $isSuperAdmin ? User::has('posts')->get(['id', 'name', 'email']) : [];
        $categoriesList = PostCategory::orderBy('nom')->get(['id', 'nom', 'slug']);

        return response()->json([
            'posts' => $posts,
            'stats' => $stats,
            'chartStats' => $chartStats,
            'categoriesStats' => $categoriesStats,
            'postsStatusStats' => $postsStatusStats,
            'topPosts' => $topPosts,
            'topAuthors' => $topAuthors,
            'engagementStats' => $engagementStats,
            'scheduledPosts' => $scheduledPosts,
            'weeklyActivity' => $weeklyActivity,
            'monthlyPostsStats' => $monthlyPostsStats,
            'hourlyPostsStats' => $hourlyPostsStats,
            'categoryPerformance' => $categoryPerformance,
            'topTags' => $topTags,
            'is_super_admin' => $isSuperAdmin,
            'authors' => $authors,
            'categories_list' => $categoriesList,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'category_id' => $request->category_id,
                'author_id' => $request->author_id,
                'period' => $request->period,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'year' => $request->year,
                'month' => $request->month,
            ],
        ]);
    }

    /**
     * Supprime un article.
     */
    public function destroy(Post $post): JsonResponse
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super_admin');

        if (! $isSuperAdmin && $post->user_id !== $user->id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Article supprimé avec succès.']);
    }

    /**
     * Duplique un article.
     */
    public function duplicate(Post $post): JsonResponse
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super_admin');

        if (! $isSuperAdmin && $post->user_id !== $user->id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $newPost = $post->replicate();
        $newPost->title = $post->title.' (Copie)';
        $newPost->slug = Str::slug($newPost->title).'-'.Str::random(5);
        $newPost->status = 'draft';
        $newPost->published_at = null;
        $newPost->save();

        return response()->json([
            'message' => 'Article dupliqué avec succès.',
            'post' => new PostResource($newPost),
        ], 201);
    }

    /**
     * Réorganise l'ordre des posts.
     */
    public function postsReorder(Request $request): JsonResponse
    {
        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'exists:posts,id',
        ]);

        foreach ($request->ordered_ids as $index => $id) {
            Post::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['message' => 'Ordre mis à jour avec succès.']);
    }

    // ─── Méthodes privées (identiques à l'original) ────────────────────

    private function applyFilters($query, Request $request, bool $isSuperAdmin, $user): void
    {
        if ($request->search) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }
        if ($request->sort) {
            $direction = $request->direction ?? 'desc';
            $query->orderBy($request->sort, $direction);
        } else {
            $query->latest();
        }
    }

    // ==================== 1. STATISTIQUES DES POSTS PAR STATUT ====================
    private function computePostsStatusStats(Request $request, bool $isSuperAdmin, $user): array
    {
        $query = Post::select('status', DB::raw('count(*) as count'))
            ->groupBy('status');

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');
        // Appliquer les filtres supplémentaires
        if ($request->status && $request->status !== 'all') {
            $query->where('posts.status', $request->status);
        }
        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        return $query->get()
            ->map(fn ($item) => [
                'status' => $item->status,
                'status_label' => $item->status_label,
                'count' => (int) $item->count,
                'fill' => match ($item->status) {
                    'published' => 'var(--chart-1)',
                    'draft' => 'var(--chart-2)',
                    'scheduled' => 'var(--chart-3)',
                    'archived' => 'var(--chart-4)',
                    'expired' => 'var(--chart-5)',
                    default => 'var(--chart-1)',
                },
            ])
            ->toArray();
    }

    // ==================== 2. STATISTIQUES DES CATÉGORIES ====================
    private function computeCategoriesStats(Request $request, bool $isSuperAdmin, $user): array
    {
        $query = PostCategory::whereHas('posts', function ($q) use ($request, $isSuperAdmin, $user) {
            if (! $isSuperAdmin) {
                $q->where('user_id', $user->id);
            }
            $q = $this->applyDateFilters($q, $request, 'posts');
            if ($request->status && $request->status !== 'all') {
                $q->where('posts.status', $request->status);
            }
            if ($request->category_id) {
                $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
            }
            if ($isSuperAdmin && $request->author_id) {
                $q->where('user_id', $request->author_id);
            }
        })
            ->withCount(['posts' => function ($q) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $q->where('user_id', $user->id);
                }
                $q = $this->applyDateFilters($q, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $q->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
                }
                if ($isSuperAdmin && $request->author_id) {
                    $q->where('user_id', $request->author_id);
                }
            }])
            ->orderBy('posts_count', 'desc');

        return $query->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'nom' => $category->nom,
                'slug' => $category->slug,
                'color' => $category->color,
                'posts_count' => $category->posts_count,
            ])
            ->toArray();
    }

    // ==================== 3. TOP 10 DES ARTICLES ====================
    private function computeTopPosts(Request $request, bool $isSuperAdmin, $user): array
    {
        $query = Post::with(['user'])
            ->where('posts.status', 'published')
            ->orderBy('posts.views_count', 'desc')
            ->limit(10);

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');

        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        return $query->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'views_count' => $post->views_count,
                'likes_count' => $post->likes_count,
                'comments_count' => $post->comments_count,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'email' => $post->user->email,
                    'avatar_url' => $post->user->avatar_url,
                ],
                'published_at' => $post->published_at?->format('Y-m-d'),
            ])
            ->toArray();
    }

    // ==================== 4. TOP CONTRIBUTEURS ====================
    private function computeTopAuthors(Request $request, $user): array
    {
        $query = User::whereHas('posts', function ($q) use ($request) {
            $q = $this->applyDateFilters($q, $request, 'posts');
            if ($request->status && $request->status !== 'all') {
                $q->where('posts.status', $request->status);
            }
            if ($request->category_id) {
                $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
            }
        })
            ->withCount(['posts' => function ($q) use ($request) {
                $q = $this->applyDateFilters($q, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $q->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
                }
            }])
            ->withSum(['posts' => function ($q) use ($request) {
                $q = $this->applyDateFilters($q, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $q->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
                }
            }], 'views_count')
            ->orderBy('posts_count', 'desc')
            ->limit(10);

        return $query->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
                'posts_count' => $user->posts_count,
                'total_views' => $user->posts_sum_views_count ?? 0,
            ])
            ->toArray();
    }

    // ==================== 5. TAUX D'ENGAGEMENT ====================
    private function computeEngagementStats(Request $request, bool $isSuperAdmin, $user)
    {
        $query = Post::where('posts.status', 'published')
            ->selectRaw('AVG((posts.likes_count + posts.comments_count) * 1.0 / NULLIF(posts.views_count, 0) * 100) as avg_engagement')
            ->selectRaw('MAX((posts.likes_count + posts.comments_count) * 1.0 / NULLIF(posts.views_count, 0) * 100) as max_engagement');

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');

        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        return $query->first();
    }

    // ==================== 6. ARTICLES PROGRAMMÉS ====================
    private function computeScheduledPosts(Request $request, bool $isSuperAdmin, $user): array
    {
        $query = Post::where('posts.status', 'scheduled')
            ->where('posts.scheduled_for', '>=', now())
            ->where('posts.scheduled_for', '<=', now()->addDays(30))
            ->orderBy('posts.scheduled_for');

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');

        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        return $query->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'scheduled_for' => $post->scheduled_for,
            ])
            ->toArray();
    }

    // ==================== 8. ACTIVITÉ PAR JOUR ====================
    private function computeWeeklyActivity(Request $request, bool $isSuperAdmin, $user, string $driver): array
    {
        $query = Post::selectRaw($this->getDayOfWeekExpression($driver).' as day_num, COUNT(*) as count')
            ->where('posts.status', 'published')
            ->groupBy($driver === 'pgsql' ? 'day_num' : 'strftime(\'%w\', posts.created_at)')
            ->orderBy('day_num');

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');

        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        return $query->get()
            ->map(fn ($item) => [
                'day' => $this->translateDayNumber((int) $item->day_num, $driver),
                'count' => (int) $item->count,
            ])
            ->toArray();
    }

    // ==================== 9. PUBLICATIONS PAR MOIS ====================
    private function computeMonthlyStats(Request $request, bool $isSuperAdmin, $user, string $driver): array
    {
        $query = Post::selectRaw($this->getMonthExpression($driver).' as month, COUNT(*) as count')
            ->where('posts.status', 'published')
            ->groupBy($driver === 'pgsql' ? 'month' : 'strftime(\'%m\', posts.created_at)')
            ->orderBy('month');

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');

        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        return $query->get()
            ->map(fn ($item) => [
                'month' => (int) $item->month,
                'month_name' => $this->getMonthName((int) $item->month),
                'count' => (int) $item->count,
            ])
            ->toArray();
    }

    // ==================== 10. HEURES DE PUBLICATION ====================
    private function computeHourlyStats(Request $request, bool $isSuperAdmin, $user, string $driver): array
    {
        $query = Post::selectRaw($this->getHourExpression($driver).' as hour, COUNT(*) as count')
            ->where('posts.status', 'published')
            ->groupBy($driver === 'pgsql' ? 'hour' : 'strftime(\'%H\', posts.created_at)')
            ->orderBy('hour');

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');

        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        return $query->get()
            ->map(fn ($item) => [
                'hour' => (int) $item->hour,
                'count' => (int) $item->count,
            ])
            ->toArray();
    }

    // ==================== 11. PERFORMANCE DES CATÉGORIES ====================
    private function computeCategoryPerformance(Request $request, bool $isSuperAdmin, $user): array
    {
        $query = PostCategory::whereHas('posts', function ($q) use ($request, $isSuperAdmin, $user) {
            if (! $isSuperAdmin) {
                $q->where('user_id', $user->id);
            }
            $q = $this->applyDateFilters($q, $request, 'posts');
            if ($request->status && $request->status !== 'all') {
                $q->where('posts.status', $request->status);
            }
            if ($request->category_id) {
                $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
            }
            if ($isSuperAdmin && $request->author_id) {
                $q->where('user_id', $request->author_id);
            }
        })
            ->withCount(['posts' => function ($q) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $q->where('user_id', $user->id);
                }
                $q = $this->applyDateFilters($q, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $q->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
                }
                if ($isSuperAdmin && $request->author_id) {
                    $q->where('user_id', $request->author_id);
                }
            }])
            ->withSum(['posts' => function ($q) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $q->where('user_id', $user->id);
                }
                $q = $this->applyDateFilters($q, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $q->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
                }
                if ($isSuperAdmin && $request->author_id) {
                    $q->where('user_id', $request->author_id);
                }
            }], 'views_count')
            ->withSum(['posts' => function ($q) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $q->where('user_id', $user->id);
                }
                $q = $this->applyDateFilters($q, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $q->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
                }
                if ($isSuperAdmin && $request->author_id) {
                    $q->where('user_id', $request->author_id);
                }
            }], 'likes_count')
            ->withSum(['posts' => function ($q) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $q->where('user_id', $user->id);
                }
                $q = $this->applyDateFilters($q, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $q->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $q->whereHas('categories', fn ($c) => $c->where('posts_categories.id', $request->category_id));
                }
                if ($isSuperAdmin && $request->author_id) {
                    $q->where('user_id', $request->author_id);
                }
            }], 'comments_count')
            ->orderBy('posts_count', 'desc')
            ->limit(10);

        return $query->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'nom' => $category->nom,
                'slug' => $category->slug,
                'posts_count' => $category->posts_count,
                'total_views' => $category->posts_sum_views_count ?? 0,
                'total_likes' => $category->posts_sum_likes_count ?? 0,
                'total_comments' => $category->posts_sum_comments_count ?? 0,
            ])
            ->toArray();
    }

    // ==================== 12. TAGS LES PLUS UTILISÉS ====================
    private function computeTopTags(Request $request, $user): array
    {
        $postsQuery = Post::query();
        $postsQuery = $this->applyDateFilters($postsQuery, $request, 'posts');

        if ($request->status && $request->status !== 'all') {
            $postsQuery->where('posts.status', $request->status);
        }
        if ($request->category_id) {
            $postsQuery->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($request->author_id) {
            $postsQuery->where('user_id', $request->author_id);
        }

        $postIds = $postsQuery->pluck('posts.id');

        if ($postIds->isEmpty()) {
            return [];
        }

        $tagCounts = DB::table('taggables')
            ->where('taggable_type', Post::class)
            ->whereIn('taggable_id', $postIds)
            ->select('tag_id', DB::raw('COUNT(*) as total'))
            ->groupBy('tag_id')
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();

        $tagIds = $tagCounts->pluck('tag_id')->toArray();
        if (empty($tagIds)) {
            return [];
        }

        $tags = DB::table('tags')->whereIn('id', $tagIds)->get()->keyBy('id');

        return $tagCounts->map(fn ($item) => [
            'id' => $item->tag_id,
            'name' => $this->extractTagName($tags[$item->tag_id]->name ?? ''),
            'slug' => $tags[$item->tag_id]->slug ?? '',
            'posts_count' => (int) $item->total,
        ])->values()->toArray();
    }

    // ==================== 13. DONNÉES POUR LE GRAPHIQUE ====================
    private function computeChartStats(Request $request, bool $isSuperAdmin, $user, string $driver): array
    {
        $dateExpression = $driver === 'pgsql'
            ? 'DATE(posts.created_at)'
            : 'date(posts.created_at)';

        $query = Post::selectRaw($dateExpression.' as date')
            ->selectRaw('SUM(posts.views_count) as views')
            ->selectRaw('SUM(posts.likes_count) as likes')
            ->selectRaw('SUM(posts.comments_count) as comments')
            ->groupBy($driver === 'pgsql' ? 'date' : 'date(posts.created_at)')
            ->orderBy('date');

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');

        if ($request->status && $request->status !== 'all') {
            $query->where('posts.status', $request->status);
        }
        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        return $query->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'views' => (int) $item->views,
                'likes' => (int) $item->likes,
                'comments' => (int) $item->comments,
            ])
            ->toArray();
    }

    // ==================== 14. STATISTIQUES GLOBALES ====================
    private function computeStats(Request $request, bool $isSuperAdmin, $user): array
    {
        // Reprendre exactement la logique de l'original pour assembler $stats, $oldDraftsCount, etc.
        // (le code de cette méthode était très long, je le reproduis ici de manière condensée mais fidèle)
        $query = Post::query();
        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }
        $query = $this->applyDateFilters($query, $request, 'posts');
        if ($request->status && $request->status !== 'all') {
            $query->where('posts.status', $request->status);
        }
        if ($request->category_id) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        $currentPeriodQuery = clone $query;
        $currentStats = [
            'total_posts' => $currentPeriodQuery->count(),
            'total_views' => $currentPeriodQuery->sum('posts.views_count'),
            'total_likes' => $currentPeriodQuery->sum('posts.likes_count'),
            'total_comments' => $currentPeriodQuery->sum('posts.comments_count'),
        ];

        // Période précédente...
        $previousPeriodQuery = Post::query();
        if (! $isSuperAdmin) {
            $previousPeriodQuery->where('user_id', $user->id);
        }
        if ($request->status && $request->status !== 'all') {
            $previousPeriodQuery->where('posts.status', $request->status);
        }
        if ($request->category_id) {
            $previousPeriodQuery->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
        }
        if ($isSuperAdmin && $request->author_id) {
            $previousPeriodQuery->where('user_id', $request->author_id);
        }

        // Détermination des dates (current/previous) selon period, start_date, end_date
        $period = $request->period;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $currentStartDate = null;
        $currentEndDate = null;
        $previousStartDate = null;
        $previousEndDate = null;

        if ($period || $startDate || $endDate) {
            switch ($period) {
                case 'today':
                    $currentStartDate = now()->startOfDay();
                    $currentEndDate = now()->endOfDay();
                    $previousStartDate = now()->subDay()->startOfDay();
                    $previousEndDate = now()->subDay()->endOfDay();
                    break;
                case 'yesterday':
                    $currentStartDate = now()->subDay()->startOfDay();
                    $currentEndDate = now()->subDay()->endOfDay();
                    $previousStartDate = now()->subDays(2)->startOfDay();
                    $previousEndDate = now()->subDays(2)->endOfDay();
                    break;
                case 'last7days':
                    $currentStartDate = now()->subDays(7);
                    $previousStartDate = now()->subDays(14);
                    $previousEndDate = now()->subDays(7);
                    break;
                case 'last30days':
                    $currentStartDate = now()->subDays(30);
                    $previousStartDate = now()->subDays(60);
                    $previousEndDate = now()->subDays(30);
                    break;
                case 'last90days':
                    $currentStartDate = now()->subDays(90);
                    $previousStartDate = now()->subDays(180);
                    $previousEndDate = now()->subDays(90);
                    break;
                case 'thisWeek':
                    $currentStartDate = now()->startOfWeek();
                    $currentEndDate = now()->endOfWeek();
                    $previousStartDate = now()->subWeek()->startOfWeek();
                    $previousEndDate = now()->subWeek()->endOfWeek();
                    break;
                case 'lastWeek':
                    $currentStartDate = now()->subWeek()->startOfWeek();
                    $currentEndDate = now()->subWeek()->endOfWeek();
                    $previousStartDate = now()->subWeeks(2)->startOfWeek();
                    $previousEndDate = now()->subWeeks(2)->endOfWeek();
                    break;
                case 'thisMonth':
                    $currentStartDate = now()->startOfMonth();
                    $currentEndDate = now()->endOfMonth();
                    $previousStartDate = now()->subMonth()->startOfMonth();
                    $previousEndDate = now()->subMonth()->endOfMonth();
                    break;
                case 'lastMonth':
                    $currentStartDate = now()->subMonth()->startOfMonth();
                    $currentEndDate = now()->subMonth()->endOfMonth();
                    $previousStartDate = now()->subMonths(2)->startOfMonth();
                    $previousEndDate = now()->subMonths(2)->endOfMonth();
                    break;
                case 'thisQuarter':
                    $currentStartDate = now()->startOfQuarter();
                    $currentEndDate = now()->endOfQuarter();
                    $previousStartDate = now()->subQuarter()->startOfQuarter();
                    $previousEndDate = now()->subQuarter()->endOfQuarter();
                    break;
                case 'lastQuarter':
                    $currentStartDate = now()->subQuarter()->startOfQuarter();
                    $currentEndDate = now()->subQuarter()->endOfQuarter();
                    $previousStartDate = now()->subQuarters(2)->startOfQuarter();
                    $previousEndDate = now()->subQuarters(2)->endOfQuarter();
                    break;
                case 'thisYear':
                    $currentStartDate = now()->startOfYear();
                    $currentEndDate = now()->endOfYear();
                    $previousStartDate = now()->subYear()->startOfYear();
                    $previousEndDate = now()->subYear()->endOfYear();
                    break;
                case 'lastYear':
                    $currentStartDate = now()->subYear()->startOfYear();
                    $currentEndDate = now()->subYear()->endOfYear();
                    $previousStartDate = now()->subYears(2)->startOfYear();
                    $previousEndDate = now()->subYears(2)->endOfYear();
                    break;
                case 'custom':
                    if ($startDate) {
                        $currentStartDate = Carbon::parse($startDate)->startOfDay();
                    }
                    if ($endDate) {
                        $currentEndDate = Carbon::parse($endDate)->endOfDay();
                    }
                    if ($currentStartDate && $currentEndDate) {
                        $duration = $currentStartDate->diffInDays($currentEndDate);
                        $previousEndDate = clone $currentStartDate;
                        $previousStartDate = clone $previousEndDate;
                        $previousStartDate->subDays($duration);
                    }
                    break;
                default:
                    $currentStartDate = now()->subDays(30);
                    $previousStartDate = now()->subDays(60);
                    $previousEndDate = now()->subDays(30);
                    break;
            }

            if ($currentStartDate) {
                $currentPeriodQuery->where('posts.created_at', '>=', $currentStartDate);
                $previousPeriodQuery->where('posts.created_at', '>=', $previousStartDate ?? $currentStartDate->copy()->subDays($currentStartDate->diffInDays($currentEndDate ?? now())));
            }
            if ($currentEndDate) {
                $currentPeriodQuery->where('posts.created_at', '<=', $currentEndDate);
                $previousPeriodQuery->where('posts.created_at', '<=', $previousEndDate ?? $currentStartDate);
            }
        }

        $previousStats = [
            'total_posts' => $previousPeriodQuery->count(),
            'total_views' => $previousPeriodQuery->sum('posts.views_count'),
            'total_likes' => $previousPeriodQuery->sum('posts.likes_count'),
            'total_comments' => $previousPeriodQuery->sum('posts.comments_count'),
        ];

        $viewsChange = $this->calculatePercentageChange($currentStats['total_views'], $previousStats['total_views']);
        $likesChange = $this->calculatePercentageChange($currentStats['total_likes'], $previousStats['total_likes']);
        $postsChange = $this->calculatePercentageChange($currentStats['total_posts'], $previousStats['total_posts']);

        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $postsThisMonth = (clone $query)->whereBetween('posts.created_at', [$currentMonthStart, $currentMonthEnd])->count();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        $postsPreviousMonth = (clone $previousPeriodQuery)->whereBetween('posts.created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $postsThisMonthChange = $this->calculatePercentageChange($postsThisMonth, $postsPreviousMonth);

        $activeAuthors = User::whereHas('posts', function ($query) use ($currentStartDate, $currentEndDate, $user, $isSuperAdmin, $request) {
            if ($currentStartDate) {
                $query->where('created_at', '>=', $currentStartDate);
            }
            if ($currentEndDate) {
                $query->where('created_at', '<=', $currentEndDate);
            }
            if (! $isSuperAdmin) {
                $query->where('user_id', $user->id);
            }
            if ($request->status && $request->status !== 'all') {
                $query->where('posts.status', $request->status);
            }
            if ($request->category_id) {
                $query->whereHas('categories', fn ($q) => $q->where('posts_categories.id', $request->category_id));
            }
        })->count();

        $activeAuthorsPrevious = User::whereHas('posts', function ($query) use ($previousStartDate, $previousEndDate) {
            if ($previousStartDate) {
                $query->where('created_at', '>=', $previousStartDate);
            }
            if ($previousEndDate) {
                $query->where('created_at', '<=', $previousEndDate);
            }
        })->count();

        $activeAuthorsChange = $this->calculatePercentageChange($activeAuthors, $activeAuthorsPrevious);

        // Anciens brouillons
        $oldDraftsCount = Post::where('posts.status', 'draft')
            ->where('posts.updated_at', '<=', now()->subDays(30))
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->count();

        // Jours depuis dernière publication
        $lastPublishedPost = Post::where('status', 'published')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->latest('published_at')
            ->first();
        $daysSinceLastPost = null;
        if ($lastPublishedPost) {
            $publishedDate = Carbon::parse($lastPublishedPost->published_at)->startOfDay();
            $currentDate = now()->startOfDay();
            $daysSinceLastPost = (int) $publishedDate->diffInDays($currentDate);
        }

        // Tendance des vues 7 jours
        $viewsLast7Days = Post::where('status', 'published')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('views_count');
        $viewsPrevious7Days = Post::where('status', 'published')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->sum('views_count');
        $viewsTrend = $this->calculatePercentageChange($viewsLast7Days, $viewsPrevious7Days);

        // Brouillons récents
        $pendingDraftsCount = Post::where('posts.status', 'draft')
            ->where('posts.updated_at', '>=', now()->subDays(7))
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->count();
        $previousDraftsCount = Post::where('posts.status', 'draft')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('posts.updated_at', [now()->subDays(14), now()->subDays(7)])
            ->count();
        $draftsChange = $this->calculatePercentageChange($pendingDraftsCount, $previousDraftsCount);

        $conversionRate = $currentStats['total_posts'] > 0
            ? round(($currentStats['total_posts'] / max(1, $previousStats['total_posts'])) * 100, 1)
            : 0;

        $engagement = $this->computeEngagementStats($request, $isSuperAdmin, $user);

        return [
            'total_posts' => $currentStats['total_posts'],
            'published_posts' => (clone $currentPeriodQuery)->where('posts.status', 'published')->count(),
            'draft_posts' => (clone $currentPeriodQuery)->where('posts.status', 'draft')->count(),
            'scheduled_posts' => (clone $currentPeriodQuery)->where('posts.status', 'scheduled')->count(),
            'archived_posts' => (clone $currentPeriodQuery)->where('posts.status', 'archived')->count(),
            'total_views' => $currentStats['total_views'],
            'total_likes' => $currentStats['total_likes'],
            'total_comments' => $currentStats['total_comments'],
            'views_change' => $viewsChange,
            'likes_change' => $likesChange,
            'posts_change' => $postsChange,
            'old_drafts_count' => $oldDraftsCount,
            'avg_engagement' => round($engagement->avg_engagement ?? 0, 2),
            'max_engagement' => round($engagement->max_engagement ?? 0, 2),
            'posts_this_month' => $postsThisMonth,
            'posts_this_month_change' => $postsThisMonthChange,
            'active_authors' => $activeAuthors,
            'active_authors_change' => $activeAuthorsChange,
            'conversion_rate' => $conversionRate,
            'days_since_last_post' => $daysSinceLastPost,
            'views_trend' => $viewsTrend,
            'pending_drafts' => $pendingDraftsCount,
            'pending_drafts_change' => $draftsChange,
        ];
    }

    // ==================== MÉTHODES D'AIDE ====================
    private function getDayOfWeekExpression(string $driver): string
    {
        return match ($driver) {
            'pgsql' => 'EXTRACT(DOW FROM posts.created_at)',
            'sqlite' => "strftime('%w', posts.created_at)",
            default => 'EXTRACT(DOW FROM posts.created_at)',
        };
    }

    private function getMonthExpression(string $driver): string
    {
        return match ($driver) {
            'pgsql' => 'EXTRACT(MONTH FROM posts.created_at)',
            'sqlite' => "strftime('%m', posts.created_at)",
            default => 'EXTRACT(MONTH FROM posts.created_at)',
        };
    }

    private function getHourExpression(string $driver): string
    {
        return match ($driver) {
            'pgsql' => 'EXTRACT(HOUR FROM posts.created_at)',
            'sqlite' => "strftime('%H', posts.created_at)",
            default => 'EXTRACT(HOUR FROM posts.created_at)',
        };
    }

    private function translateDayNumber(int $dayNum, string $driver = 'pgsql'): string
    {
        return match ($dayNum) {
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            default => 'Inconnu',
        };
    }

    private function getMonthName(int $month): string
    {
        return match ($month) {
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
            default => '',
        };
    }

    private function extractTagName($tagName): string
    {
        if (is_null($tagName)) {
            return 'Sans nom';
        }
        if (is_string($tagName) && ! str_contains($tagName, '{')) {
            return $tagName;
        }
        $decoded = json_decode($tagName, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded['fr'] ?? $decoded['en'] ?? reset($decoded) ?? 'Tag';
        }

        return trim(preg_replace('/[{}":]/', '', $tagName));
    }

    private function applyDateFilters($query, Request $request, string $table = 'posts')
    {
        $period = $request->period;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $year = $request->year;
        $month = $request->month;

        if (! $period && ! $startDate && ! $endDate && ! $year && ! $month) {
            return $query;
        }

        switch ($period) {
            case 'today':
                $query->whereDate($table.'.created_at', today());
                break;
            case 'yesterday':
                $query->whereDate($table.'.created_at', today()->subDay());
                break;
            case 'last7days':
                $query->where($table.'.created_at', '>=', now()->subDays(7));
                break;
            case 'last30days':
                $query->where($table.'.created_at', '>=', now()->subDays(30));
                break;
            case 'last90days':
                $query->where($table.'.created_at', '>=', now()->subDays(90));
                break;
            case 'thisWeek':
                $query->whereBetween($table.'.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'lastWeek':
                $query->whereBetween($table.'.created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                break;
            case 'thisMonth':
                if ($month) {
                    $query->whereMonth($table.'.created_at', $month);
                } else {
                    $query->whereMonth($table.'.created_at', now()->month);
                }
                if ($year) {
                    $query->whereYear($table.'.created_at', $year);
                } else {
                    $query->whereYear($table.'.created_at', now()->year);
                }
                break;
            case 'lastMonth':
                $lastMonth = now()->subMonth();
                $query->whereMonth($table.'.created_at', $lastMonth->month)
                    ->whereYear($table.'.created_at', $lastMonth->year);
                break;
            case 'thisQuarter':
                $query->whereBetween($table.'.created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                break;
            case 'lastQuarter':
                $lastQuarter = now()->subQuarter();
                $query->whereBetween($table.'.created_at', [$lastQuarter->startOfQuarter(), $lastQuarter->endOfQuarter()]);
                break;
            case 'thisYear':
                $query->whereYear($table.'.created_at', $year ?? now()->year);
                break;
            case 'lastYear':
                $query->whereYear($table.'.created_at', now()->subYear()->year);
                break;
            case 'custom':
                if ($startDate) {
                    $query->whereDate($table.'.created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $query->whereDate($table.'.created_at', '<=', $endDate);
                }
                break;
        }

        return $query;
    }

    private function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function hasDashboardSchema(): bool
    {
        return Schema::hasTable('posts')
            && Schema::hasTable('users')
            && Schema::hasTable('posts_categories')
            && Schema::hasTable('posts_categories_pivot');
    }

    private function emptyDashboardPayload(Request $request): array
    {
        return [
            'posts' => [
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'from' => null,
                'to' => null,
                'total' => 0,
                'per_page' => 10,
            ],
            'stats' => [
                'total_posts' => 0,
                'published_posts' => 0,
                'draft_posts' => 0,
                'scheduled_posts' => 0,
                'archived_posts' => 0,
                'total_views' => 0,
                'total_likes' => 0,
                'total_comments' => 0,
                'views_change' => 0,
                'likes_change' => 0,
                'posts_change' => 0,
                'old_drafts_count' => 0,
                'avg_engagement' => 0,
                'max_engagement' => 0,
                'posts_this_month' => 0,
                'posts_this_month_change' => 0,
                'active_authors' => 0,
                'active_authors_change' => 0,
                'conversion_rate' => 0,
                'days_since_last_post' => null,
                'views_trend' => 0,
                'pending_drafts' => 0,
                'pending_drafts_change' => 0,
            ],
            'chartStats' => [],
            'categoriesStats' => [],
            'postsStatusStats' => [],
            'topPosts' => [],
            'topAuthors' => [],
            'engagementStats' => null,
            'scheduledPosts' => [],
            'weeklyActivity' => [],
            'monthlyPostsStats' => [],
            'hourlyPostsStats' => [],
            'categoryPerformance' => [],
            'topTags' => [],
            'is_super_admin' => Auth::user()?->hasRole('super_admin') ?? false,
            'authors' => [],
            'categories_list' => [],
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'category_id' => $request->category_id,
                'author_id' => $request->author_id,
                'period' => $request->period,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'year' => $request->year,
                'month' => $request->month,
            ],
        ];
    }
}
