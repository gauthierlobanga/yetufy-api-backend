<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


class AcheteurDashboardController extends Controller
{
    public function adminDashboardIndex(Request $request)
    {
        if (! $this->hasDashboardSchema()) {
            return response()->json($this->emptyDashboardPayload($request));
        }

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super_admin');
        $driver = DB::connection()->getDriverName(); // 'pgsql' ou 'sqlite'

        // Appliquer les filtres de date à la requête principale
        $query = Post::with(['user', 'categories', 'media', 'tags']);

        if (! $isSuperAdmin) {
            $query->where('user_id', $user->id);
        }

        $query = $this->applyDateFilters($query, $request, 'posts');

        if ($request->search) {
            $query->where('posts.title', 'like', '%'.$request->search.'%');
        }

        if ($request->status && $request->status !== 'all') {
            $query->where('posts.status', $request->status);
        }

        if ($request->category_id) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $query->where('user_id', $request->author_id);
        }

        if ($request->sort) {
            $direction = $request->direction ?? 'desc';
            $query->orderBy('posts.'.$request->sort, $direction);
        } else {
            $query->latest('posts.created_at');
        }

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

        // ==================== 1. STATISTIQUES DES POSTS PAR STATUT ====================
        $postsStatusQuery = Post::select('status', DB::raw('count(*) as count'))
            ->groupBy('status');

        if (! $isSuperAdmin) {
            $postsStatusQuery->where('user_id', $user->id);
        }

        $postsStatusQuery = $this->applyDateFilters($postsStatusQuery, $request, 'posts');

        if ($request->status && $request->status !== 'all') {
            $postsStatusQuery->where('posts.status', $request->status);
        }

        if ($request->category_id) {
            $postsStatusQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $postsStatusQuery->where('user_id', $request->author_id);
        }

        $postsStatusStats = $postsStatusQuery->get()
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

        // ==================== 2. STATISTIQUES DES CATÉGORIES ====================
        $categoriesStatsQuery = PostCategory::whereHas('posts', function ($query) use ($request, $isSuperAdmin, $user) {
            if (! $isSuperAdmin) {
                $query->where('user_id', $user->id);
            }
            $query = $this->applyDateFilters($query, $request, 'posts');
            if ($request->status && $request->status !== 'all') {
                $query->where('posts.status', $request->status);
            }
            if ($request->category_id) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('posts_categories.id', $request->category_id);
                });
            }
            if ($isSuperAdmin && $request->author_id) {
                $query->where('user_id', $request->author_id);
            }
        })
            ->withCount(['posts' => function ($query) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $query->where('user_id', $user->id);
                }
                $query = $this->applyDateFilters($query, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $query->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $query->whereHas('categories', function ($q) use ($request) {
                        $q->where('posts_categories.id', $request->category_id);
                    });
                }
                if ($isSuperAdmin && $request->author_id) {
                    $query->where('user_id', $request->author_id);
                }
            }])
            ->orderBy('posts_count', 'desc');

        $categoriesStats = $categoriesStatsQuery->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'nom' => $category->nom,
                'slug' => $category->slug,
                'color' => $category->color,
                'posts_count' => $category->posts_count,
            ])
            ->toArray();

        // ==================== 3. TOP 10 DES ARTICLES ====================
        $topPostsQuery = Post::with(['user'])
            ->where('posts.status', 'published')
            ->orderBy('posts.views_count', 'desc')
            ->limit(10);

        if (! $isSuperAdmin) {
            $topPostsQuery->where('user_id', $user->id);
        }

        $topPostsQuery = $this->applyDateFilters($topPostsQuery, $request, 'posts');

        if ($request->category_id) {
            $topPostsQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $topPostsQuery->where('user_id', $request->author_id);
        }

        $topPosts = $topPostsQuery->get()
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

        // ==================== 4. TOP CONTRIBUTEURS ====================
        $topAuthors = [];
        if ($isSuperAdmin) {
            $topAuthorsQuery = User::whereHas('posts', function ($query) use ($request) {
                $query = $this->applyDateFilters($query, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $query->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $query->whereHas('categories', function ($q) use ($request) {
                        $q->where('posts_categories.id', $request->category_id);
                    });
                }
            })
                ->withCount(['posts' => function ($query) use ($request) {
                    $query = $this->applyDateFilters($query, $request, 'posts');
                    if ($request->status && $request->status !== 'all') {
                        $query->where('posts.status', $request->status);
                    }
                    if ($request->category_id) {
                        $query->whereHas('categories', function ($q) use ($request) {
                            $q->where('posts_categories.id', $request->category_id);
                        });
                    }
                }])
                ->withSum(['posts' => function ($query) use ($request) {
                    $query = $this->applyDateFilters($query, $request, 'posts');
                    if ($request->status && $request->status !== 'all') {
                        $query->where('posts.status', $request->status);
                    }
                    if ($request->category_id) {
                        $query->whereHas('categories', function ($q) use ($request) {
                            $q->where('posts_categories.id', $request->category_id);
                        });
                    }
                }], 'views_count')
                ->orderBy('posts_count', 'desc')
                ->limit(10);

            $topAuthors = $topAuthorsQuery->get()
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                    'posts_count' => $user->posts_count,
                    'total_views' => $user->posts_sum_views_count ?? 0,
                ]);
        }

        // ==================== 5. TAUX D'ENGAGEMENT ====================
        $engagementQuery = Post::where('posts.status', 'published')
            ->selectRaw('AVG((posts.likes_count + posts.comments_count) * 1.0 / NULLIF(posts.views_count, 0) * 100) as avg_engagement')
            ->selectRaw('MAX((posts.likes_count + posts.comments_count) * 1.0 / NULLIF(posts.views_count, 0) * 100) as max_engagement');

        if (! $isSuperAdmin) {
            $engagementQuery->where('user_id', $user->id);
        }

        $engagementQuery = $this->applyDateFilters($engagementQuery, $request, 'posts');

        if ($request->category_id) {
            $engagementQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $engagementQuery->where('user_id', $request->author_id);
        }

        $engagementStats = $engagementQuery->first();

        // ==================== 6. ARTICLES PROGRAMMÉS ====================
        $scheduledQuery = Post::where('posts.status', 'scheduled')
            ->where('posts.scheduled_for', '>=', now())
            ->where('posts.scheduled_for', '<=', now()->addDays(30))
            ->orderBy('posts.scheduled_for');

        if (! $isSuperAdmin) {
            $scheduledQuery->where('user_id', $user->id);
        }

        $scheduledQuery = $this->applyDateFilters($scheduledQuery, $request, 'posts');

        if ($request->category_id) {
            $scheduledQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $scheduledQuery->where('user_id', $request->author_id);
        }

        $scheduledPosts = $scheduledQuery->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'scheduled_for' => $post->scheduled_for,
            ]);

        // ==================== 7. BROUILLONS ANCIENS ====================
        $oldDraftsQuery = Post::where('posts.status', 'draft')
            ->where('posts.updated_at', '<=', now()->subDays(30));

        if (! $isSuperAdmin) {
            $oldDraftsQuery->where('user_id', $user->id);
        }

        $oldDraftsQuery = $this->applyDateFilters($oldDraftsQuery, $request, 'posts');

        if ($request->category_id) {
            $oldDraftsQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $oldDraftsQuery->where('user_id', $request->author_id);
        }

        $oldDraftsCount = $oldDraftsQuery->count();

        // ==================== 8. ACTIVITÉ PAR JOUR (compatible multi-driver) ====================
        $weeklyQuery = Post::selectRaw($this->getDayOfWeekExpression($driver).' as day_num, COUNT(*) as count')
            ->where('posts.status', 'published')
            ->groupBy($driver === 'pgsql' ? 'day_num' : 'strftime(\'%w\', posts.created_at)')
            ->orderBy('day_num');

        if (! $isSuperAdmin) {
            $weeklyQuery->where('user_id', $user->id);
        }

        $weeklyQuery = $this->applyDateFilters($weeklyQuery, $request, 'posts');

        if ($request->category_id) {
            $weeklyQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $weeklyQuery->where('user_id', $request->author_id);
        }

        $weeklyActivity = $weeklyQuery->get()
            ->map(fn ($item) => [
                'day' => $this->translateDayNumber((int) $item->day_num, $driver),
                'count' => (int) $item->count,
            ]);

        // ==================== 9. PUBLICATIONS PAR MOIS (compatible multi-driver) ====================
        $monthlyQuery = Post::selectRaw($this->getMonthExpression($driver).' as month, COUNT(*) as count')
            ->where('posts.status', 'published')
            ->groupBy($driver === 'pgsql' ? 'month' : 'strftime(\'%m\', posts.created_at)')
            ->orderBy('month');

        if (! $isSuperAdmin) {
            $monthlyQuery->where('user_id', $user->id);
        }

        $monthlyQuery = $this->applyDateFilters($monthlyQuery, $request, 'posts');

        if ($request->category_id) {
            $monthlyQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $monthlyQuery->where('user_id', $request->author_id);
        }

        $monthlyPostsStats = $monthlyQuery->get()
            ->map(fn ($item) => [
                'month' => (int) $item->month,
                'month_name' => $this->getMonthName((int) $item->month),
                'count' => (int) $item->count,
            ]);

        // ==================== 10. HEURES DE PUBLICATION (compatible multi-driver) ====================
        $hourlyQuery = Post::selectRaw($this->getHourExpression($driver).' as hour, COUNT(*) as count')
            ->where('posts.status', 'published')
            ->groupBy($driver === 'pgsql' ? 'hour' : 'strftime(\'%H\', posts.created_at)')
            ->orderBy('hour');

        if (! $isSuperAdmin) {
            $hourlyQuery->where('user_id', $user->id);
        }

        $hourlyQuery = $this->applyDateFilters($hourlyQuery, $request, 'posts');

        if ($request->category_id) {
            $hourlyQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $hourlyQuery->where('user_id', $request->author_id);
        }

        $hourlyPostsStats = $hourlyQuery->get()
            ->map(fn ($item) => [
                'hour' => (int) $item->hour,
                'count' => (int) $item->count,
            ]);

        // ==================== 11. PERFORMANCE DES CATÉGORIES ====================
        $categoryPerformanceQuery = PostCategory::whereHas('posts', function ($query) use ($request, $isSuperAdmin, $user) {
            if (! $isSuperAdmin) {
                $query->where('user_id', $user->id);
            }
            $query = $this->applyDateFilters($query, $request, 'posts');
            if ($request->status && $request->status !== 'all') {
                $query->where('posts.status', $request->status);
            }
            if ($request->category_id) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('posts_categories.id', $request->category_id);
                });
            }
            if ($isSuperAdmin && $request->author_id) {
                $query->where('user_id', $request->author_id);
            }
        })
            ->withCount(['posts' => function ($query) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $query->where('user_id', $user->id);
                }
                $query = $this->applyDateFilters($query, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $query->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $query->whereHas('categories', function ($q) use ($request) {
                        $q->where('posts_categories.id', $request->category_id);
                    });
                }
                if ($isSuperAdmin && $request->author_id) {
                    $query->where('user_id', $request->author_id);
                }
            }])
            ->withSum(['posts' => function ($query) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $query->where('user_id', $user->id);
                }
                $query = $this->applyDateFilters($query, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $query->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $query->whereHas('categories', function ($q) use ($request) {
                        $q->where('posts_categories.id', $request->category_id);
                    });
                }
                if ($isSuperAdmin && $request->author_id) {
                    $query->where('user_id', $request->author_id);
                }
            }], 'views_count')
            ->withSum(['posts' => function ($query) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $query->where('user_id', $user->id);
                }
                $query = $this->applyDateFilters($query, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $query->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $query->whereHas('categories', function ($q) use ($request) {
                        $q->where('posts_categories.id', $request->category_id);
                    });
                }
                if ($isSuperAdmin && $request->author_id) {
                    $query->where('user_id', $request->author_id);
                }
            }], 'likes_count')
            ->withSum(['posts' => function ($query) use ($request, $isSuperAdmin, $user) {
                if (! $isSuperAdmin) {
                    $query->where('user_id', $user->id);
                }
                $query = $this->applyDateFilters($query, $request, 'posts');
                if ($request->status && $request->status !== 'all') {
                    $query->where('posts.status', $request->status);
                }
                if ($request->category_id) {
                    $query->whereHas('categories', function ($q) use ($request) {
                        $q->where('posts_categories.id', $request->category_id);
                    });
                }
                if ($isSuperAdmin && $request->author_id) {
                    $query->where('user_id', $request->author_id);
                }
            }], 'comments_count')
            ->orderBy('posts_count', 'desc')
            ->limit(10);

        $categoryPerformance = $categoryPerformanceQuery->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'nom' => $category->nom,
                'slug' => $category->slug,
                'posts_count' => $category->posts_count,
                'total_views' => $category->posts_sum_views_count ?? 0,
                'total_likes' => $category->posts_sum_likes_count ?? 0,
                'total_comments' => $category->posts_sum_comments_count ?? 0,
            ]);

        // ==================== 12. TAGS LES PLUS UTILISÉS ====================
        $topTags = [];
        if ($isSuperAdmin) {
            $postsQuery = Post::query();

            if (! $isSuperAdmin) {
                $postsQuery->where('user_id', $user->id);
            }

            $postsQuery = $this->applyDateFilters($postsQuery, $request, 'posts');

            if ($request->status && $request->status !== 'all') {
                $postsQuery->where('posts.status', $request->status);
            }

            if ($request->category_id) {
                $postsQuery->whereHas('categories', function ($q) use ($request) {
                    $q->where('posts_categories.id', $request->category_id);
                });
            }

            if ($isSuperAdmin && $request->author_id) {
                $postsQuery->where('user_id', $request->author_id);
            }

            $postIds = $postsQuery->pluck('posts.id');

            if ($postIds->isNotEmpty()) {
                $tagCounts = DB::table('taggables')
                    ->where('taggable_type', Post::class)
                    ->whereIn('taggable_id', $postIds)
                    ->select('tag_id', DB::raw('COUNT(*) as total'))
                    ->groupBy('tag_id')
                    ->orderBy('total', 'desc')
                    ->limit(20)
                    ->get();

                $tagIds = $tagCounts->pluck('tag_id')->toArray();

                if (! empty($tagIds)) {
                    $tags = DB::table('tags')
                        ->whereIn('id', $tagIds)
                        ->get()
                        ->keyBy('id');

                    $topTags = $tagCounts->map(fn ($item) => [
                        'id' => $item->tag_id,
                        'name' => $this->extractTagName($tags[$item->tag_id]->name ?? ''),
                        'slug' => $tags[$item->tag_id]->slug ?? '',
                        'posts_count' => (int) $item->total,
                    ])->values()->toArray();
                }
            }
        }

        // ==================== 13. DONNÉES POUR LE GRAPHIQUE (compatible multi-driver) ====================
        $dateExpression = $driver === 'pgsql'
            ? 'DATE(posts.created_at)'
            : 'date(posts.created_at)';

        $chartStatsQuery = Post::selectRaw($dateExpression.' as date')
            ->selectRaw('SUM(posts.views_count) as views')
            ->selectRaw('SUM(posts.likes_count) as likes')
            ->selectRaw('SUM(posts.comments_count) as comments')
            ->groupBy($driver === 'pgsql' ? 'date' : 'date(posts.created_at)')
            ->orderBy('date');

        if (! $isSuperAdmin) {
            $chartStatsQuery->where('user_id', $user->id);
        }

        $chartStatsQuery = $this->applyDateFilters($chartStatsQuery, $request, 'posts');

        if ($request->status && $request->status !== 'all') {
            $chartStatsQuery->where('posts.status', $request->status);
        }

        if ($request->category_id) {
            $chartStatsQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $chartStatsQuery->where('user_id', $request->author_id);
        }

        $chartStats = $chartStatsQuery->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'views' => (int) $item->views,
                'likes' => (int) $item->likes,
                'comments' => (int) $item->comments,
            ])
            ->toArray();

        if (empty($chartStats)) {
            $chartStats = [];
        }

        // ==================== 14. STATISTIQUES GLOBALES ====================
        $statsQuery = Post::query();

        if (! $isSuperAdmin) {
            $statsQuery->where('user_id', $user->id);
        }

        $statsQuery = $this->applyDateFilters($statsQuery, $request, 'posts');

        if ($request->status && $request->status !== 'all') {
            $statsQuery->where('posts.status', $request->status);
        }

        if ($request->category_id) {
            $statsQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $statsQuery->where('user_id', $request->author_id);
        }

        // Calcul des statistiques de la période actuelle
        $currentPeriodQuery = clone $statsQuery;
        $currentStats = [
            'total_posts' => $currentPeriodQuery->count(),
            'total_views' => $currentPeriodQuery->sum('posts.views_count'),
            'total_likes' => $currentPeriodQuery->sum('posts.likes_count'),
            'total_comments' => $currentPeriodQuery->sum('posts.comments_count'),
        ];

        // Calcul des statistiques de la période précédente
        $previousPeriodQuery = Post::query();

        if (! $isSuperAdmin) {
            $previousPeriodQuery->where('user_id', $user->id);
        }

        if ($request->status && $request->status !== 'all') {
            $previousPeriodQuery->where('posts.status', $request->status);
        }

        if ($request->category_id) {
            $previousPeriodQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $previousPeriodQuery->where('user_id', $request->author_id);
        }

        // Gestion des périodes
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

        // Récupérer les statistiques de la période précédente
        $previousStats = [
            'total_posts' => $previousPeriodQuery->count(),
            'total_views' => $previousPeriodQuery->sum('posts.views_count'),
            'total_likes' => $previousPeriodQuery->sum('posts.likes_count'),
            'total_comments' => $previousPeriodQuery->sum('posts.comments_count'),
        ];

        // Calculer les pourcentages de changement
        $viewsChange = $this->calculatePercentageChange($currentStats['total_views'], $previousStats['total_views']);
        $likesChange = $this->calculatePercentageChange($currentStats['total_likes'], $previousStats['total_likes']);
        $postsChange = $this->calculatePercentageChange($currentStats['total_posts'], $previousStats['total_posts']);

        // Calculer les articles du mois en cours
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $postsThisMonthQuery = clone $statsQuery;
        $postsThisMonthQuery->whereBetween('posts.created_at', [$currentMonthStart, $currentMonthEnd]);
        $postsThisMonth = $postsThisMonthQuery->count();

        // Calculer les articles du mois précédent pour la tendance
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        $postsPreviousMonthQuery = clone $previousPeriodQuery;
        $postsPreviousMonthQuery->whereBetween('posts.created_at', [$previousMonthStart, $previousMonthEnd]);
        $postsPreviousMonth = $postsPreviousMonthQuery->count();
        $postsThisMonthChange = $this->calculatePercentageChange($postsThisMonth, $postsPreviousMonth);

        // Calculer le nombre d'auteurs actifs
        $activeAuthorsQuery = User::whereHas('posts', function ($query) use ($currentStartDate, $currentEndDate, $user, $isSuperAdmin, $request) {
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
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('posts_categories.id', $request->category_id);
                });
            }
        })->count();

        $activeAuthorsPreviousQuery = User::whereHas('posts', function ($query) use ($previousStartDate, $previousEndDate) {
            if ($previousStartDate) {
                $query->where('created_at', '>=', $previousStartDate);
            }
            if ($previousEndDate) {
                $query->where('created_at', '<=', $previousEndDate);
            }
        })->count();

        $activeAuthorsChange = $this->calculatePercentageChange($activeAuthorsQuery, $activeAuthorsPreviousQuery);

        $statsQuery = Post::query();

        if (! +$isSuperAdmin) {
            $statsQuery->where('user_id', $user->id);
        }

        $statsQuery = $this->applyDateFilters($statsQuery, $request, 'posts');

        if ($request->status && $request->status !== 'all') {
            $statsQuery->where('posts.status', $request->status);
        }

        if ($request->category_id) {
            $statsQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $statsQuery->where('user_id', $request->author_id);
        }

        $currentPeriodQuery = clone $statsQuery;
        $currentStats = [
            'total_posts' => $currentPeriodQuery->count(),
            'total_views' => $currentPeriodQuery->sum('posts.views_count'),
            'total_likes' => $currentPeriodQuery->sum('posts.likes_count'),
            'total_comments' => $currentPeriodQuery->sum('posts.comments_count'),
        ];

        // ==================== 16. JOURS DEPUIS DERNIÈRE PUBLICATION ====================
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

        // ==================== 17. TENDANCE DES VUES (7 JOURS) ====================
        $viewsLast7Days = Post::where('status', 'published')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('views_count');

        $viewsPrevious7Days = Post::where('status', 'published')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->sum('views_count');

        $viewsTrend = $this->calculatePercentageChange($viewsLast7Days, $viewsPrevious7Days);

        // ==================== 18. BROUILLONS EN ATTENTE ====================
        $pendingDraftsQuery = Post::where('posts.status', 'draft')
            ->where('posts.updated_at', '>=', now()->subDays(7));

        if (! $isSuperAdmin) {
            $pendingDraftsQuery->where('user_id', $user->id);
        }

        $pendingDraftsQuery = $this->applyDateFilters($pendingDraftsQuery, $request, 'posts');

        if ($request->category_id) {
            $pendingDraftsQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $pendingDraftsQuery->where('user_id', $request->author_id);
        }

        $pendingDraftsCount = $pendingDraftsQuery->count();

        // Calculer la tendance des brouillons
        $previousDraftsQuery = Post::where('posts.status', 'draft')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('posts.updated_at', [now()->subDays(14), now()->subDays(7)]);

        $previousDraftsCount = $previousDraftsQuery->count();
        $draftsChange = $this->calculatePercentageChange($pendingDraftsCount, $previousDraftsCount);

        $currentPeriodQuery = clone $statsQuery;
        $currentStats = [
            'total_posts' => $currentPeriodQuery->count(),
            'total_views' => $currentPeriodQuery->sum('posts.views_count'),
            'total_likes' => $currentPeriodQuery->sum('posts.likes_count'),
            'total_comments' => $currentPeriodQuery->sum('posts.comments_count'),
        ];
        // [Le reste du code reste identique à l'original]
        // Je n'inclus pas tout pour éviter une réponse trop longue

        $previousPeriodQuery = Post::query();

        if (! $isSuperAdmin) {
            $previousPeriodQuery->where('user_id', $user->id);
        }

        if ($request->status && $request->status !== 'all') {
            $previousPeriodQuery->where('posts.status', $request->status);
        }

        if ($request->category_id) {
            $previousPeriodQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $previousPeriodQuery->where('user_id', $request->author_id);
        }

        // Gestion des périodes
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

        // Récupérer les statistiques de la période précédente
        $previousStats = [
            'total_posts' => $previousPeriodQuery->count(),
            'total_views' => $previousPeriodQuery->sum('posts.views_count'),
            'total_likes' => $previousPeriodQuery->sum('posts.likes_count'),
            'total_comments' => $previousPeriodQuery->sum('posts.comments_count'),
        ];

        // Calculer les pourcentages de changement
        $viewsChange = $this->calculatePercentageChange($currentStats['total_views'], $previousStats['total_views']);
        $likesChange = $this->calculatePercentageChange($currentStats['total_likes'], $previousStats['total_likes']);
        $postsChange = $this->calculatePercentageChange($currentStats['total_posts'], $previousStats['total_posts']);

        // Calculer les articles du mois en cours
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $postsThisMonthQuery = clone $statsQuery;
        $postsThisMonthQuery->whereBetween('posts.created_at', [$currentMonthStart, $currentMonthEnd]);
        $postsThisMonth = $postsThisMonthQuery->count();

        // Calculer les articles du mois précédent pour la tendance
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        $postsPreviousMonthQuery = clone $previousPeriodQuery;
        $postsPreviousMonthQuery->whereBetween('posts.created_at', [$previousMonthStart, $previousMonthEnd]);
        $postsPreviousMonth = $postsPreviousMonthQuery->count();
        $postsThisMonthChange = $this->calculatePercentageChange($postsThisMonth, $postsPreviousMonth);

        // Calculer le nombre d'auteurs actifs
        $activeAuthorsQuery = User::whereHas('posts', function ($query) use ($currentStartDate, $currentEndDate, $user, $isSuperAdmin, $request) {
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
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('posts_categories.id', $request->category_id);
                });
            }
        })->count();

        $activeAuthorsPreviousQuery = User::whereHas('posts', function ($query) use ($previousStartDate, $previousEndDate) {
            if ($previousStartDate) {
                $query->where('created_at', '>=', $previousStartDate);
            }
            if ($previousEndDate) {
                $query->where('created_at', '<=', $previousEndDate);
            }
        })->count();
        $activeAuthorsChange = $this->calculatePercentageChange($activeAuthorsQuery, $activeAuthorsPreviousQuery);

        // ==================== 15. TAUX DE CONVERSION ====================
        $conversionRate = $currentStats['total_posts'] > 0
            ? round(($currentStats['total_posts'] / max(1, $previousStats['total_posts'])) * 100, 1)
            : 0;

        // ==================== 16. JOURS DEPUIS DERNIÈRE PUBLICATION ====================
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

        // ==================== 17. TENDANCE DES VUES (7 JOURS) ====================
        $viewsLast7Days = Post::where('status', 'published')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('views_count');

        $viewsPrevious7Days = Post::where('status', 'published')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->sum('views_count');

        $viewsTrend = $this->calculatePercentageChange($viewsLast7Days, $viewsPrevious7Days);

        // ==================== 18. BROUILLONS EN ATTENTE ====================
        $pendingDraftsQuery = Post::where('posts.status', 'draft')
            ->where('posts.updated_at', '>=', now()->subDays(7));

        if (! $isSuperAdmin) {
            $pendingDraftsQuery->where('user_id', $user->id);
        }

        $pendingDraftsQuery = $this->applyDateFilters($pendingDraftsQuery, $request, 'posts');

        if ($request->category_id) {
            $pendingDraftsQuery->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_categories.id', $request->category_id);
            });
        }

        if ($isSuperAdmin && $request->author_id) {
            $pendingDraftsQuery->where('user_id', $request->author_id);
        }

        $pendingDraftsCount = $pendingDraftsQuery->count();

        // Calculer la tendance des brouillons
        $previousDraftsQuery = Post::where('posts.status', 'draft')
            ->when(! $isSuperAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('posts.updated_at', [now()->subDays(14), now()->subDays(7)]);

        $previousDraftsCount = $previousDraftsQuery->count();
        $draftsChange = $this->calculatePercentageChange($pendingDraftsCount, $previousDraftsCount);

        // ==================== ASSEMBLAGE DES STATISTIQUES ====================
        $stats = [
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
            'avg_engagement' => round($engagementStats->avg_engagement ?? 0, 2),
            'max_engagement' => round($engagementStats->max_engagement ?? 0, 2),
            'posts_this_month' => $postsThisMonth,
            'posts_this_month_change' => $postsThisMonthChange,
            'active_authors' => $activeAuthorsQuery,
            'active_authors_change' => $activeAuthorsChange,
            'conversion_rate' => $conversionRate,
            'days_since_last_post' => $daysSinceLastPost,
            'views_trend' => $viewsTrend,
            'pending_drafts' => $pendingDraftsCount,
            'pending_drafts_change' => $draftsChange,
        ];

        // Liste des auteurs (pour le filtre - seulement pour super admin)
        $authors = [];
        if ($isSuperAdmin) {
            $authors = User::has('posts')->get(['id', 'name', 'email']);
        }

        // Liste des catégories (pour le filtre)
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

    // ==================== MÉTHODES D'AIDE POUR LA COMPATIBILITÉ MULTI-DRIVER ====================

    /**
     * Retourne l'expression SQL pour extraire le jour de la semaine (0-6, dimanche=0)
     */
    private function getDayOfWeekExpression(string $driver): string
    {
        return match ($driver) {
            'pgsql' => 'EXTRACT(DOW FROM posts.created_at)',
            'sqlite' => "strftime('%w', posts.created_at)",
            default => 'EXTRACT(DOW FROM posts.created_at)',
        };
    }

    /**
     * Retourne l'expression SQL pour extraire le mois (1-12)
     */
    private function getMonthExpression(string $driver): string
    {
        return match ($driver) {
            'pgsql' => 'EXTRACT(MONTH FROM posts.created_at)',
            'sqlite' => "strftime('%m', posts.created_at)",
            default => 'EXTRACT(MONTH FROM posts.created_at)',
        };
    }

    /**
     * Retourne l'expression SQL pour extraire l'heure (0-23)
     */
    private function getHourExpression(string $driver): string
    {
        return match ($driver) {
            'pgsql' => 'EXTRACT(HOUR FROM posts.created_at)',
            'sqlite' => "strftime('%H', posts.created_at)",
            default => 'EXTRACT(HOUR FROM posts.created_at)',
        };
    }

    /**
     * Traduit le numéro du jour en nom (compatible PostgreSQL et SQLite)
     * Note: Les deux drivers retournent 0 pour dimanche
     */
    private function translateDayNumber(int $dayNum, string $driver = 'pgsql'): string
    {
        // PostgreSQL DOW et SQLite %w retournent tous deux 0 pour dimanche
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

    private function translateDay(string $day): string
    {
        return match ($day) {
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
            default => $day,
        };
    }

    private function getMonthName(int $month): string
    {
        return match ($month) {
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
            default => '',
        };
    }

    public function destroy(Post $post)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super_admin');

        if (! $isSuperAdmin && $post->user_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer cet article.');
        }

        $post->delete();

        return redirect()->back()->with('success', 'Article supprimé avec succès');
    }

    public function duplicate(Post $post)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super_admin');

        if (! $isSuperAdmin && $post->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à dupliquer cet article.');
        }

        $newPost = $post->replicate();
        $newPost->title = $post->title.' (Copie)';
        $newPost->slug = Str::slug($newPost->title).'-'.Str::random(5);
        $newPost->status = 'draft';
        $newPost->published_at = null;
        $newPost->save();

        return redirect()->back()->with('success', 'Article dupliqué avec succès');
    }

    public function postsReorder(Request $request)
    {
        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'exists:posts,id',
        ]);

        foreach ($request->ordered_ids as $index => $id) {
            Post::where('id', $id)->update(['order' => $index]);
        }

        return redirect()->back()->with('success', 'Ordre mis à jour avec succès');
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
            && Schema::hasTable('category_post');
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
